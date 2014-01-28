<?php
/**
 * The Notification System.
 *
 * Puts notifications on top of wpCSL plugin admin pages.
 *
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2013 Charleston Software Associates
 * @package wpCSL/Notifications
 *
 */
class wpCSL_notifications__slplus {

    /**
     * @var wpCSL_notifications_notice__slplus[] $notices and array of notice boxes.
     */
    private $notices = null;

    /**
     * Build a new notification object.
     *
     * @param type $params
     */
    function __construct($params) {
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * Add a notification to the notice stack
     *
     * @param mixed $level - int (1 severe, 9 info) string 'error','warning','info'
     * @param string $content - the message
     * @param string $link - url
     */
    function add_notice($level = 1, $content='', $link = null) {
        
        // Set numeric level for string input
        //
        switch ($level):
            case 'error':
                $level = 1;
                break;
            case 'warning':
                $level = 5;
                break;
            case 'info':
                $level = 9;
                break;
        endswitch;

        $this->notices[] = new wpCSL_notifications_notice__slplus(
            array(
                'level' => $level,
                'content' => $content,
                'link' => $link
            )
        );
    }

    /**
     * Render the notices to the browser page.
     */
    function display() {
        echo $this->get();
    }

   /**
    * Return a formatted HTML string representing the notification.
    *
    * @param boolean $simple - set to true to see simplified unformatted notices.
    * @return string - the HTML or simple string output
    */
   function get($simple=false) {

        // No need to do anything if there aren't any notices
        if (!isset($this->notices) ) { return; }
        if ($this->notices === null) { return; }

        foreach ($this->notices as $notice) {
            $levels[$notice->level][] = $notice;
        }

        ksort($levels, SORT_NUMERIC);
        $difference = max(array_keys($levels));

        $notice_output = '';
        $actionMessage = __('needs attention',WPCSL__slplus__VERSION);
        foreach ($levels as $key => $value) {
            if (!$simple) {
                switch ($difference) {
                case 1:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 60, 60);'>\n";
                    break;
                case 1:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 102, 0);'>\n";
                    break;
                case 4:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 204, 0);'>\n";
                    break;
                case 3:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 165, 104);'>\n";
                    break;
                case 2:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 165, 0);'>\n";
                    break;
                case 5:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 201, 202);'>\n";
                    break;
                case 6:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(224, 255, 255);'>\n";
                    break;
                case 7:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(144, 238, 144);'>\n";
                    break;
                case 9:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(250, 250, 210);'>\n";
                    $actionMessage = __('wants you to know',WPCSL__slplus__VERSION);
                    break;
                case 8:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(245, 222, 179);'>\n";
                    break;
                default:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(245, 245, 220);'>\n";
                    break;
                }
                $notice_output .= sprintf(
                    __('<p><strong><a href="%s">%s</a> %s: </strong>',WPCSL__slplus__VERSION),
                    $this->url,
                    $this->name,
                    $actionMessage
                );
                $notice_output .= "<ul>\n";
            }
            foreach ($value as $notice) {
                if (!$simple) { $notice_output .= '<li>'; }
                $notice_output .= $notice->display();
                if (!$simple) { $notice_output .= '</li>'; }
                $notice_output .= "\n";
            }
            if (!$simple) {
                $notice_output .= "</ul>\n";
                $notice_output .= "</p></div>\n";
            }
        }

        return $notice_output;
    }

    /**
     * Reset the notices to a blank array.
     */
    function delete_all_notices() {
        $this->notices = null;
    }
}

/**
 * This class represents each individual notice.
 *
 */
class wpCSL_notifications_notice__slplus {

    function __construct($params) {
        foreach($params as $name => $value) {
            $this->$name = $value;
        }
    }

    function display() {
        $retval = $this->content;
        if ( isset($this->link)     && 
             !is_null($this->link)  && 
             ($this->link != '')
            ) {
           $retval .= " (<a href=\"{$this->link}\">Details</a>)";
        }
        return $retval;
    }
}

?>
