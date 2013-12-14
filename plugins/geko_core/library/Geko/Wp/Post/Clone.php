<?php

//
class Geko_Wp_Post_Clone extends Geko_Wp_Initialize
{
	
	protected $_sButtonTitle = 'Clone Post';
	protected $_sLinkTitle = 'Clone this post';
	

	//
	public function addAdmin() {
		
		add_action( 'admin_init', array( $this, 'doClone' ) );
		add_action( 'admin_init', array( $this, 'doCloneDebug' ) );
		
		if (
			( 'edit' == $_REQUEST[ 'action' ] ) && 
			( $iPostId = $_REQUEST[ 'post' ] ) && 
			( $this->isCorrectType( $iPostId ) )
		) {
			add_action( 'post_submitbox_start', array( $this, 'doCloneButton' ) );
		}
		
		$this->adminHooks();
	}
	
	//
	public function adminHooks() {
		add_filter( 'post_row_actions', array( $this, 'doCloneLink' ), 10, 2 );	
	}
	
	
	//// action methods
	
	//
	public function doCloneLink( $aActions, $oPost ) {
		
		$sActUrl = $this->getCloneUrl( $oPost->ID );
		
		$aActions[ 'geko_clone' ] = sprintf(
			'<a href="%s" title="%s" target="_blank">%s</a>',
			$sActUrl,
			$this->_sLinkTitle,
			'Clone'
		);
		
		return $aActions;
	}
	
	
	//
	public function doClone() {
		
		if (
			( 'geko_clone' == $_REQUEST[ 'action' ] ) && 
			( $iPostId = $_REQUEST[ 'post' ] ) && 
			( $this->isCorrectType( $iPostId ) )
		) {
			
			$iCopyId = $this->clonePost( $iPostId );
			
			wp_redirect( admin_url( 'post.php?action=edit&post=' . $iCopyId ) );
			
			die();
		}
		
	}

	//
	public function doCloneDebug() {
	
		if (
			( 'geko_clone_debug' == $_REQUEST[ 'action' ] ) && 
			( $iPostId = $_REQUEST[ 'post' ] ) && 
			( $this->isCorrectType( $iPostId ) )
		) {
			
			$this->debugClonePost( $iPostId );
			
			die();
		}
		
	}
	
	//
	public function doCloneButton() {
		?>
		<div><a href="<?php echo $this->getCloneUrl( $_REQUEST[ 'post' ] ); ?>"><?php echo $this->_sButtonTitle; ?></a></div>
		<?php
	}
	
	
	//// functionality
	
	//
	public function getCloneUrl( $iPostId ) {
		return sprintf(
			'%s/wp-admin/post.php?post=%d&action=geko_clone',
			Geko_Wp::getUrl(),
			$iPostId
		);
	}
	
	//
	public function isCorrectType( $iPostId ) {
		return ( 'post' == $this->getType( $iPostId ) ) ? TRUE : FALSE ;
	}
	
	
	//
	public function getType( $iPostId ) {
		
		global $wpdb;
		
		return $wpdb->get_var( $wpdb->prepare(
			"SELECT post_type FROM $wpdb->posts WHERE ID = %d",
			$iPostId
		) );
	}
	
	//
	public function clonePost( $iPostId ) {
		
		global $wpdb;
		
		//// basic data
		
		$aOrigData = $this->getPostData( $iPostId );
		
		$aInsertData = array(
			'post_content' => $aOrigData[ 'post_content' ],
			'post_title' => $aOrigData[ 'post_title' ] . ' Copy',
			'post_excerpt' => $aOrigData[ 'post_excerpt' ],
			'post_status' => $aOrigData[ 'post_status' ],
			'comment_status' => $aOrigData[ 'comment_status' ],
			'ping_status' => $aOrigData[ 'ping_status' ],
			'post_password' => $aOrigData[ 'post_password' ],
			'post_parent' => $aOrigData[ 'post_parent' ],
			'post_type' => $aOrigData[ 'post_type' ]
		);
		
		$iCopyId = wp_insert_post( $aInsertData );
		
		//// categories
		
		$aOrigCats = wp_get_post_categories( $iPostId );
		wp_set_post_terms( $iCopyId, $aOrigCats, 'category' );
					
		//// attachments
		
		$aOrigAttHash = array();		// hash of new attachment to original attachment
		$aOrigAttFile = array();		// hash of original file paths
		
		$aAtts = $this->getPostAttachments( $iPostId );
		foreach ( $aAtts as $aRow ) {
			
			$aFile = $this->formatForSideload( $aRow );
						
			$iAttId = media_handle_sideload( $aFile, $iCopyId, '', array(
				'post_content' => $aRow[ 'post_content' ],
				'post_title' => $aRow[ 'post_title' ],
				'post_excerpt' => $aRow[ 'post_excerpt' ]
			) );
			
			$aNewRow = $this->getPostData( $iAttId );
			
			$aOrigAttHash[ $aRow[ 'ID' ] ] = $iAttId;
			$aOrigAttFile[ $aRow[ 'guid' ] ] = $aNewRow[ 'guid' ];
		}
		
		
		$aPostMetaSkip = array( '_wp_attachment_metadata', '_wp_attached_file' );
		foreach ( $aOrigAttHash as $iOrigAttId => $iAttId ) {
			
			// clean-up first
			$aMeta = $this->getPostMeta( $iAttId );
			foreach ( $aMeta as $aRow ) {
				
				$sMetaKey = $aRow[ 'meta_key' ];
				
				if ( !in_array( $sMetaKey, $aPostMetaSkip ) ) {
					$wpdb->delete(
						$wpdb->postmeta,
						array( 'post_id' => $iAttId, 'meta_key' => $sMetaKey ),
						array( '%d', '%s' )
					);
				}
			}
			
			// copy original
			$aMeta = $this->getPostMeta( $iOrigAttId );
			foreach ( $aMeta as $aRow ) {
				
				$sMetaKey = $aRow[ 'meta_key' ];
				
				if ( !in_array( $sMetaKey, $aPostMetaSkip ) ) {
					$sMetaValue = $aRow[ 'meta_value' ];
					add_post_meta( $iAttId, $sMetaKey, $sMetaValue );
				}
			}
			
		}
		
		
		//// get image picker fields
		$aAttachmentFlds = $this->getAttachmentFields( $iPostId );
		$aImgPckFlds = $aAttachmentFlds[ 'imgpck_field' ];
		$aFileFlds = $aAttachmentFlds[ 'attachment_field' ];
		
		
		//// postmeta
		
		// clean-up first
		$wpdb->delete(
			$wpdb->postmeta,
			array( 'post_id' => $iCopyId ),
			array( '%d' )
		);
		
		$aPostMetaSkip = array( '_edit_last', '_edit_lock' );
		
		$aOrigMetaIdHash = array();
		$aMeta = $this->getPostMeta( $iPostId );
		
		foreach ( $aMeta as $aRow ) {
			
			$sMetaKey = $aRow[ 'meta_key' ];
			
			if ( !in_array( $sMetaKey, $aPostMetaSkip ) ) {
				
				$sMetaValue = $aRow[ 'meta_value' ];
				
				if ( in_array( $sMetaKey, $aImgPckFlds ) ) {
					// re-assign images
					try {
						$aNewVals = array();
						$aVals = Zend_Json::decode( $sMetaValue );
						if ( is_array( $aVals ) ) {
							foreach ( $aVals as $iOldAttId ) {
								if ( $iNewAttId = $aOrigAttHash[ $iOldAttId ] ) {
									$aNewVals[] = strval( $iNewAttId );
								} else {
									$aNewVals[] = $iOldAttId;			// keep old value
								}
							}
						}
						$sMetaValue = Zend_Json::encode( $aNewVals );
					} catch ( Exception $e ) {
					
					}
				}
				
				if ( in_array( $sMetaKey, $aFileFlds ) ) {
					// re-assign path
					if ( $sNewPath = $aOrigAttFile[ $sMetaValue ] ) {
						$sMetaValue = $sNewPath;
					}
				}
				
				add_post_meta( $iCopyId, $sMetaKey, $sMetaValue );
				$aOrigMetaIdHash[ $aRow[ 'meta_id' ] ] = $wpdb->insert_id;
			}
		}
		
		
		//// post meta members
		
		$aMetaIds = $this->getMetaIds( $aMeta );
		$aMetaKeyHash = $this->getMetaKeyHash( $aMeta );
		
		$aMetaMembers = $this->getPostMetaMembers( $aMetaIds );
		
		foreach ( $aMetaMembers as $aRow ) {
			
			$iMetaId = $aRow[ 'meta_id' ];
			
			if ( $iNewMetaId = $aOrigMetaIdHash[ $iMetaId ] ) {
				
				$iMemberId = $aRow[ 'member_id' ];
				$sMetaKey = $aMetaKeyHash[ $iMetaId ];
				
				if ( in_array( $sMetaKey, $aImgPckFlds ) ) {
					// re-assign image
					if ( $iNewAttId = $aOrigAttHash[ $iMemberId ] ) {
						$iMemberId = $iNewAttId;
					}
				}
				
				$wpdb->insert(
					$wpdb->geko_post_meta_members,
					array(
						'meta_id' => $iNewMetaId,
						'member_id' => $iMemberId,
						'member_value' => $aRow[ 'member_value' ],
						'flags' => $aRow[ 'flags' ]
					),
					array( '%d', '%d', '%s', '%s' )
				);
			}
			
		}
		
		return $iCopyId;
	}
	
	
	
	//
	public function getPostData( $iPostId ) {
		
		global $wpdb;
		
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $wpdb->posts WHERE ID = %d",
			$iPostId
		), ARRAY_A );
	}
	
	//
	public function getPostAttachments( $iPostId ) {
		
		global $wpdb;
		
		$sPath = substr( ABSPATH, 0, strlen( ABSPATH ) - 1 );
		$sUrl = Geko_Wp::getUrl();
		
		$aAtts = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'attachment'",
			$iPostId
		), ARRAY_A );
		
		foreach ( $aAtts as $i => $aRow ) {
			$aAtts[ $i ][ 'path' ] = str_replace( $sUrl, $sPath, $aRow[ 'guid' ] );
		}
		
		return $aAtts;
	}
	
	//
	public function formatForSideload( $aPost ) {
		
		$sDirName = dirname( $aPost[ 'path' ] );
		$sBaseName = basename( $aPost[ 'path' ] );
		
		$sTmpFile = md5( $aPost[ 'path' ] . time() );
		$sTmpName = $sDirName . '/' . $sTmpFile;
		
		copy( $aPost[ 'path' ], $sTmpName );
		
		return array(
			'name' => $sBaseName,
			'type' => $aPost[ 'post_mime_type' ],
			'tmp_name' => $sTmpName,
			'size' => filesize( $aPost[ 'path' ] ),
			'error' => 0
		);
	}
	
	//
	public function getPostMeta( $iPostId ) {
		
		global $wpdb;
		
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $wpdb->postmeta WHERE post_id = %d",
			$iPostId
		), ARRAY_A );	
	}
	
	//
	public function getMetaIds( $aMeta ) {
		$aMetaIds = array();
		foreach ( $aMeta as $aRow ) {
			$aMetaIds[] = $aRow[ 'meta_id' ];
		}
		return $aMetaIds;
	}
	
	//
	public function getMetaKeyHash( $aMeta ) {
		$aMetaKeyHash = array();
		foreach ( $aMeta as $aRow ) {
			$aMetaKeyHash[ $aRow[ 'meta_id' ] ] = $aRow[ 'meta_key' ];
		}
		return $aMetaKeyHash;
	}
	
	
	//
	public function getPostMetaMembers( $aMetaIds ) {
		
		global $wpdb;
		
		return $wpdb->get_results(
			"SELECT * FROM $wpdb->geko_post_meta_members WHERE meta_id IN ( " . implode( ', ', $aMetaIds ) . " )"
		, ARRAY_A );
	}
	
	
	//
	public function getAttachmentFields( $iPostId ) {
		
		$aAttachmentFlds = array(
			'imgpck_field' => array(),
			'attachment_field' => array()
		);
		
		if ( $oMeta = $this->getMetaInstance( $iPostId ) ) {
			
			ob_start();
			$oMeta->outputForm();
			$sOut = ob_get_contents();
			ob_end_clean();
			
			$aAttachmentFlds = array();
			
			$oDoc = phpQuery::newDocument( $sOut );
			$aAttFlds = $oDoc->find( '.imgpck_field, .attachment_field' );
			foreach ( $aAttFlds as $oInput ) {

				$oPqInput = pq( $oInput );
				
				$sGroup = '';
				if ( $oPqInput->hasClass( 'imgpck_field' ) ) {
					$sGroup = 'imgpck_field';
				} elseif ( $oPqInput->hasClass( 'attachment_field' ) ) {
					$sGroup = 'attachment_field';
				}
				
				$aAttachmentFlds[ $sGroup ][] = $oPqInput->attr( 'name' );
			}
		}
		
		return $aAttachmentFlds;	
	}
	
	//
	public function getMetaInstance( $iPostId ) {
		
		if ( class_exists( 'Gloc_Post_Meta' ) ) {
			
			$oMeta = Gloc_Post_Meta::getInstance();
			$oMeta->setPostId( $iPostId )->getCurPost();
			
			return $oMeta;
		}
		
		return NULL;
	}
	
	
	//
	public function debugClonePost( $iPostId ) {

		echo '<pre>';

		print_r( $this->getPostData( $iPostId ) );			
		echo "\n\n";
		
		print_r( $this->getPostAttachments( $iPostId ) );			
		echo "\n\n";
		
		$aMeta = $this->getPostMeta( $iPostId );
		print_r( $aMeta );
		echo "\n\n";
		
		$aMetaIds = $this->getMetaIds( $aMeta );
		print_r( $aMetaIds );
		echo "\n\n";
		
		print_r( wp_get_post_categories( $iPostId ) );
		echo "\n\n";
		
		print_r( $this->getPostMetaMembers( $aMetaIds ) );
		echo "\n\n";
		
		print_r( $this->getAttachmentFields( $iPostId ) );
		echo "\n\n";
		
		echo '</pre>';
		
	}
	
	
	
}



