( function( $ ) {

	$.gekoGeoCoords = function() {
		
		// world cities
		var aWorld = [ {
			city: 'Aberdeen',
			country: 'Scotland',
			lat: 57.9,
			lon: -2.9
		}, {
			city: 'Adelaide',
			country: 'Australia',
			lat: -34.55,
			lon: 138.36
		}, {
			city: 'Algiers',
			country: 'Algeria',
			lat: 36.5,
			lon: 3
		}, {
			city: 'Amsterdam',
			country: 'Netherlands',
			lat: 52.22,
			lon: 4.53
		}, {
			city: 'Ankara',
			country: 'Turkey',
			lat: 39.55,
			lon: 32.55
		}, {
			city: 'Asuncion',
			country: 'Paraguay',
			lat: -25.15,
			lon: -57.4
		}, {
			city: 'Athens',
			country: 'Greece',
			lat: 37.58,
			lon: 23.43
		}, {
			city: 'Auckland',
			country: 'New Zealand',
			lat: -36.52,
			lon: 174.45
		}, {
			city: 'Bangkok',
			country: 'Thailand',
			lat: 13.45,
			lon: 100.3
		}, {
			city: 'Barcelona',
			country: 'Spain',
			lat: 41.23,
			lon: 2.9
		}, {
			city: 'Beijing',
			country: 'China',
			lat: 39.55,
			lon: 116.25
		}, {
			city: 'Belem',
			country: 'Brazil',
			lat: -1.28,
			lon: -48.29
		}, {
			city: 'Belfast',
			country: 'Northern Ireland',
			lat: 54.37,
			lon: -5.56
		}, {
			city: 'Belgrade',
			country: 'Serbia',
			lat: 44.52,
			lon: 20.32
		}, {
			city: 'Berlin',
			country: 'Germany',
			lat: 52.3,
			lon: 13.25
		}, {
			city: 'Birmingham',
			country: 'England',
			lat: 52.25,
			lon: -1.55
		}, {
			city: 'Bogota',
			country: 'Colombia',
			lat: 4.32,
			lon: -74.15
		}, {
			city: 'Bombay',
			country: 'India',
			lat: 19,
			lon: 72.48
		}, {
			city: 'Bordeaux',
			country: 'France',
			lat: 44.5,
			lon: -0.31
		}, {
			city: 'Bremen',
			country: 'Germany',
			lat: 53.5,
			lon: 8.49
		}, {
			city: 'Brisbane',
			country: 'Australia',
			lat: -27.29,
			lon: 153.8
		}, {
			city: 'Bristol',
			country: 'England',
			lat: 51.28,
			lon: -2.35
		}, {
			city: 'Brussels',
			country: 'Belgium',
			lat: 50.52,
			lon: 4.22
		}, {
			city: 'Bucharest',
			country: 'Romania',
			lat: 44.25,
			lon: 26.7
		}, {
			city: 'Budapest',
			country: 'Hungary',
			lat: 47.3,
			lon: 19.5
		}, {
			city: 'Buenos Aires',
			country: 'Argentina',
			lat: -34.35,
			lon: -58.22
		}, {
			city: 'Cairo',
			country: 'Egypt',
			lat: 30.2,
			lon: 31.21
		}, {
			city: 'Calcutta',
			country: 'India',
			lat: 22.34,
			lon: 88.24
		}, {
			city: 'Canton',
			country: 'China',
			lat: 23.7,
			lon: 113.15
		}, {
			city: 'Cape Town',
			country: 'South Africa',
			lat: -33.55,
			lon: 18.22
		}, {
			city: 'Caracas',
			country: 'Venezuela',
			lat: 10.28,
			lon: -67.2
		}, {
			city: 'Cayenne',
			country: 'French Guiana',
			lat: 4.49,
			lon: -52.18
		}, {
			city: 'Chihuahua',
			country: 'Mexico',
			lat: 28.37,
			lon: -106.5
		}, {
			city: 'Chongqing',
			country: 'China',
			lat: 29.46,
			lon: 106.34
		}, {
			city: 'Copenhagen',
			country: 'Denmark',
			lat: 55.4,
			lon: 12.34
		}, {
			city: 'Cordoba',
			country: 'Argentina',
			lat: -31.28,
			lon: -64.1
		}, {
			city: 'Dakar',
			country: 'Senegal',
			lat: 14.4,
			lon: -17.28
		}, {
			city: 'Darwin',
			country: 'Australia',
			lat: -12.28,
			lon: 130.51
		}, {
			city: 'Djibouti',
			country: 'Djibouti',
			lat: 11.3,
			lon: 43.3
		}, {
			city: 'Dublin',
			country: 'Ireland',
			lat: 53.2,
			lon: -6.15
		}, {
			city: 'Durban',
			country: 'South Africa',
			lat: -29.53,
			lon: 30.53
		}, {
			city: 'Edinburgh',
			country: 'Scotland',
			lat: 55.55,
			lon: -3.1
		}, {
			city: 'Frankfurt',
			country: 'Germany',
			lat: 50.7,
			lon: 8.41
		}, {
			city: 'Georgetown',
			country: 'Guyana',
			lat: 6.45,
			lon: -58.15
		}, {
			city: 'Glasgow',
			country: 'Scotland',
			lat: 55.5,
			lon: -4.15
		}, {
			city: 'Guatemala City',
			country: 'Guatemala',
			lat: 14.37,
			lon: -90.31
		}, {
			city: 'Guayaquil',
			country: 'Ecuador',
			lat: -2.1,
			lon: -79.56
		}, {
			city: 'Hamburg',
			country: 'Germany',
			lat: 53.33,
			lon: 10.2
		}, {
			city: 'Hammerfest',
			country: 'Norway',
			lat: 70.38,
			lon: 23.38
		}, {
			city: 'Havana',
			country: 'Cuba',
			lat: 23.8,
			lon: -82.23
		}, {
			city: 'Helsinki',
			country: 'Finland',
			lat: 60.1,
			lon: 25
		}, {
			city: 'Hobart',
			country: 'Tasmania',
			lat: -42.52,
			lon: 147.19
		}, {
			city: 'Hong Kong',
			country: 'China',
			lat: 22.2,
			lon: 114.11
		}, {
			city: 'Iquique',
			country: 'Chile',
			lat: -20.1,
			lon: -70.7
		}, {
			city: 'Irkutsk',
			country: 'Russia',
			lat: 52.3,
			lon: 104.2
		}, {
			city: 'Jakarta',
			country: 'Indonesia',
			lat: -6.16,
			lon: 106.48
		}, {
			city: 'Johannesburg',
			country: 'South Africa',
			lat: -26.12,
			lon: 28.4
		}, {
			city: 'Kingston',
			country: 'Jamaica',
			lat: 17.59,
			lon: -76.49
		}, {
			city: 'Kinshasa',
			country: 'Congo',
			lat: -4.18,
			lon: 15.17
		}, {
			city: 'Kuala Lumpur',
			country: 'Malaysia',
			lat: 3.8,
			lon: 101.42
		}, {
			city: 'La Paz',
			country: 'Bolivia',
			lat: -16.27,
			lon: -68.22
		}, {
			city: 'Leeds',
			country: 'England',
			lat: 53.45,
			lon: -1.3
		}, {
			city: 'Lima',
			country: 'Peru',
			lat: -12,
			lon: -77.2
		}, {
			city: 'Lisbon',
			country: 'Portugal',
			lat: 38.44,
			lon: -9.9
		}, {
			city: 'Liverpool',
			country: 'England',
			lat: 53.25,
			lon: -3
		}, {
			city: 'London',
			country: 'England',
			lat: 51.32,
			lon: -0.5
		}, {
			city: 'Lyons',
			country: 'France',
			lat: 45.45,
			lon: 4.5
		}, {
			city: 'Madrid',
			country: 'Spain',
			lat: 40.26,
			lon: -3.42
		}, {
			city: 'Manchester',
			country: 'England',
			lat: 53.3,
			lon: -2.15
		}, {
			city: 'Manila',
			country: 'Philippines',
			lat: 14.35,
			lon: 120.57
		}, {
			city: 'Marseilles',
			country: 'France',
			lat: 43.2,
			lon: 5.2
		}, {
			city: 'Mazatlan',
			country: 'Mexico',
			lat: 23.12,
			lon: -106.25
		}, {
			city: 'Mecca',
			country: 'Saudi Arabia',
			lat: 21.29,
			lon: 39.45
		}, {
			city: 'Melbourne',
			country: 'Australia',
			lat: -37.47,
			lon: 144.58
		}, {
			city: 'Mexico City',
			country: 'Mexico',
			lat: 19.26,
			lon: -99.7
		}, {
			city: 'Milan',
			country: 'Italy',
			lat: 45.27,
			lon: 9.1
		}, {
			city: 'Montevideo',
			country: 'Uruguay',
			lat: -34.53,
			lon: -56.1
		}, {
			city: 'Moscow',
			country: 'Russia',
			lat: 55.45,
			lon: 37.36
		}, {
			city: 'Munich',
			country: 'Germany',
			lat: 48.8,
			lon: 11.35
		}, {
			city: 'Nagasaki',
			country: 'Japan',
			lat: 32.48,
			lon: 129.57
		}, {
			city: 'Nagoya',
			country: 'Japan',
			lat: 35.7,
			lon: 136.56
		}, {
			city: 'Nairobi',
			country: 'Kenya',
			lat: -1.25,
			lon: 36.55
		}, {
			city: 'Nanjing (Nanking)',
			country: 'China',
			lat: 32.3,
			lon: 118.53
		}, {
			city: 'Naples',
			country: 'Italy',
			lat: 40.5,
			lon: 14.15
		}, {
			city: 'New Delhi',
			country: 'India',
			lat: 28.35,
			lon: 77.12
		}, {
			city: 'Newcastle-on-Tyne',
			country: 'England',
			lat: 54.58,
			lon: -1.37
		}, {
			city: 'Odessa',
			country: 'Ukraine',
			lat: 46.27,
			lon: 30.48
		}, {
			city: 'Osaka',
			country: 'Japan',
			lat: 34.32,
			lon: 135.3
		}, {
			city: 'Oslo',
			country: 'Norway',
			lat: 59.57,
			lon: 10.42
		}, {
			city: 'Panama City',
			country: 'Panama',
			lat: 8.58,
			lon: -79.32
		}, {
			city: 'Paramaribo',
			country: 'Suriname',
			lat: 5.45,
			lon: -55.15
		}, {
			city: 'Paris',
			country: 'France',
			lat: 48.48,
			lon: 2.2
		}, {
			city: 'Perth',
			country: 'Australia',
			lat: -31.57,
			lon: 115.52
		}, {
			city: 'Plymouth',
			country: 'England',
			lat: 50.25,
			lon: -4.5
		}, {
			city: 'Port Moresby',
			country: 'Papua New Guinea',
			lat: -9.25,
			lon: 147.8
		}, {
			city: 'Prague',
			country: 'Czech Republic',
			lat: 50.5,
			lon: 14.26
		}, {
			city: 'Rangoon',
			country: 'Myanmar',
			lat: 16.5,
			lon: 96
		}, {
			city: 'Reykjavik',
			country: 'Iceland',
			lat: 64.4,
			lon: -21.58
		}, {
			city: 'Rio de Janeiro',
			country: 'Brazil',
			lat: -22.57,
			lon: -43.12
		}, {
			city: 'Rome',
			country: 'Italy',
			lat: 41.54,
			lon: 12.27
		}, {
			city: 'Salvador',
			country: 'Brazil',
			lat: -12.56,
			lon: -38.27
		}, {
			city: 'Santiago',
			country: 'Chile',
			lat: -33.28,
			lon: -70.45
		}, {
			city: 'St. Petersburg',
			country: 'Russia',
			lat: 59.56,
			lon: 30.18
		}, {
			city: 'Sao Paulo',
			country: 'Brazil',
			lat: -23.31,
			lon: -46.31
		}, {
			city: 'Shanghai',
			country: 'China',
			lat: 31.1,
			lon: 121.28
		}, {
			city: 'Singapore',
			country: 'Singapore',
			lat: 1.14,
			lon: 103.55
		}, {
			city: 'Sofia',
			country: 'Bulgaria',
			lat: 42.4,
			lon: 23.2
		}, {
			city: 'Stockholm',
			country: 'Sweden',
			lat: 59.17,
			lon: 18.3
		}, {
			city: 'Sydney',
			country: 'Australia',
			lat: -34,
			lon: 151
		}, {
			city: 'Tananarive',
			country: 'Madagascar',
			lat: -18.5,
			lon: 47.33
		}, {
			city: 'Teheran',
			country: 'Iran',
			lat: 35.45,
			lon: 51.45
		}, {
			city: 'Tokyo',
			country: 'Japan',
			lat: 35.4,
			lon: 139.45
		}, {
			city: 'Tripoli',
			country: 'Libya',
			lat: 32.57,
			lon: 13.12
		}, {
			city: 'Venice',
			country: 'Italy',
			lat: 45.26,
			lon: 12.2
		}, {
			city: 'Veracruz',
			country: 'Mexico',
			lat: 19.1,
			lon: -96.1
		}, {
			city: 'Vienna',
			country: 'Austria',
			lat: 48.14,
			lon: 16.2
		}, {
			city: 'Vladivostok',
			country: 'Russia',
			lat: 43.1,
			lon: 132
		}, {
			city: 'Warsaw',
			country: 'Poland',
			lat: 52.14,
			lon: 21
		}, {
			city: 'Wellington',
			country: 'New Zealand',
			lat: -41.17,
			lon: 174.47
		}, {
			city: 'Zurich',
			country: 'Switzerland',
			lat: 47.21,
			lon: 8.31
		} ];
		
		
		// US cities
		var aUs = [ {
			city: 'Albany',
			prov: 'N.Y.',
			country: 'United States',
			lat: 42.4,
			lon: -73.45
		}, {
			city: 'Albuquerque',
			prov: 'N.M.',
			country: 'United States',
			lat: 35.5,
			lon: -106.39
		}, {
			city: 'Amarillo',
			prov: 'Tex.',
			country: 'United States',
			lat: 35.11,
			lon: -101.5
		}, {
			city: 'Anchorage',
			prov: 'Alaska',
			country: 'United States',
			lat: 61.13,
			lon: -149.54
		}, {
			city: 'Atlanta',
			prov: 'Ga.',
			country: 'United States',
			lat: 33.45,
			lon: -84.23
		}, {
			city: 'Austin',
			prov: 'Tex.',
			country: 'United States',
			lat: 30.16,
			lon: -97.44
		}, {
			city: 'Baker',
			prov: 'Ore.',
			country: 'United States',
			lat: 44.47,
			lon: -117.5
		}, {
			city: 'Baltimore',
			prov: 'Md.',
			country: 'United States',
			lat: 39.18,
			lon: -76.38
		}, {
			city: 'Bangor',
			prov: 'Maine',
			country: 'United States',
			lat: 44.48,
			lon: -68.47
		}, {
			city: 'Birmingham',
			prov: 'Ala.',
			country: 'United States',
			lat: 33.3,
			lon: -86.5
		}, {
			city: 'Bismarck',
			prov: 'N.D.',
			country: 'United States',
			lat: 46.48,
			lon: -100.47
		}, {
			city: 'Boise',
			prov: 'Idaho',
			country: 'United States',
			lat: 43.36,
			lon: -116.13
		}, {
			city: 'Boston',
			prov: 'Mass.',
			country: 'United States',
			lat: 42.21,
			lon: -71.5
		}, {
			city: 'Buffalo',
			prov: 'N.Y.',
			country: 'United States',
			lat: 42.55,
			lon: -78.5
		}, {
			city: 'Carlsbad',
			prov: 'N.M.',
			country: 'United States',
			lat: 32.26,
			lon: -104.15
		}, {
			city: 'Charleston',
			prov: 'S.C.',
			country: 'United States',
			lat: 32.47,
			lon: -79.56
		}, {
			city: 'Charleston',
			prov: 'W. Va.',
			country: 'United States',
			lat: 38.21,
			lon: -81.38
		}, {
			city: 'Charlotte',
			prov: 'N.C.',
			country: 'United States',
			lat: 35.14,
			lon: -80.5
		}, {
			city: 'Cheyenne',
			prov: 'Wyo.',
			country: 'United States',
			lat: 41.9,
			lon: -104.52
		}, {
			city: 'Chicago',
			prov: 'Ill.',
			country: 'United States',
			lat: 41.5,
			lon: -87.37
		}, {
			city: 'Cincinnati',
			prov: 'Ohio',
			country: 'United States',
			lat: 39.8,
			lon: -84.3
		}, {
			city: 'Cleveland',
			prov: 'Ohio',
			country: 'United States',
			lat: 41.28,
			lon: -81.37
		}, {
			city: 'Columbia',
			prov: 'S.C.',
			country: 'United States',
			lat: 34,
			lon: -81.2
		}, {
			city: 'Columbus',
			prov: 'Ohio',
			country: 'United States',
			lat: 40,
			lon: -83.1
		}, {
			city: 'Dallas',
			prov: 'Tex.',
			country: 'United States',
			lat: 32.46,
			lon: -96.46
		}, {
			city: 'Denver',
			prov: 'Colo.',
			country: 'United States',
			lat: 39.45,
			lon: -105
		}, {
			city: 'Des Moines',
			prov: 'Iowa',
			country: 'United States',
			lat: 41.35,
			lon: -93.37
		}, {
			city: 'Detroit',
			prov: 'Mich.',
			country: 'United States',
			lat: 42.2,
			lon: -83.3
		}, {
			city: 'Dubuque',
			prov: 'Iowa',
			country: 'United States',
			lat: 42.31,
			lon: -90.4
		}, {
			city: 'Duluth',
			prov: 'Minn.',
			country: 'United States',
			lat: 46.49,
			lon: -92.5
		}, {
			city: 'Eastport',
			prov: 'Maine',
			country: 'United States',
			lat: 44.54,
			lon: -67
		}, {
			city: 'El Centro',
			prov: 'Calif.',
			country: 'United States',
			lat: 32.38,
			lon: -115.33
		}, {
			city: 'El Paso',
			prov: 'Tex.',
			country: 'United States',
			lat: 31.46,
			lon: -106.29
		}, {
			city: 'Eugene',
			prov: 'Ore.',
			country: 'United States',
			lat: 44.3,
			lon: -123.5
		}, {
			city: 'Fargo',
			prov: 'N.D.',
			country: 'United States',
			lat: 46.52,
			lon: -96.48
		}, {
			city: 'Flagstaff',
			prov: 'Ariz.',
			country: 'United States',
			lat: 35.13,
			lon: -111.41
		}, {
			city: 'Fort Worth',
			prov: 'Tex.',
			country: 'United States',
			lat: 32.43,
			lon: -97.19
		}, {
			city: 'Fresno',
			prov: 'Calif.',
			country: 'United States',
			lat: 36.44,
			lon: -119.48
		}, {
			city: 'Grand Junction',
			prov: 'Colo.',
			country: 'United States',
			lat: 39.5,
			lon: -108.33
		}, {
			city: 'Grand Rapids',
			prov: 'Mich.',
			country: 'United States',
			lat: 42.58,
			lon: -85.4
		}, {
			city: 'Havre',
			prov: 'Mont.',
			country: 'United States',
			lat: 48.33,
			lon: -109.43
		}, {
			city: 'Helena',
			prov: 'Mont.',
			country: 'United States',
			lat: 46.35,
			lon: -112.2
		}, {
			city: 'Honolulu',
			prov: 'Hawaii',
			country: 'United States',
			lat: 21.18,
			lon: -157.5
		}, {
			city: 'Hot Springs',
			prov: 'Ark.',
			country: 'United States',
			lat: 34.31,
			lon: -93.3
		}, {
			city: 'Houston',
			prov: 'Tex.',
			country: 'United States',
			lat: 29.45,
			lon: -95.21
		}, {
			city: 'Idaho Falls',
			prov: 'Idaho',
			country: 'United States',
			lat: 43.3,
			lon: -112.1
		}, {
			city: 'Indianapolis',
			prov: 'Ind.',
			country: 'United States',
			lat: 39.46,
			lon: -86.1
		}, {
			city: 'Jackson',
			prov: 'Miss.',
			country: 'United States',
			lat: 32.2,
			lon: -90.12
		}, {
			city: 'Jacksonville',
			prov: 'Fla.',
			country: 'United States',
			lat: 30.22,
			lon: -81.4
		}, {
			city: 'Juneau',
			prov: 'Alaska',
			country: 'United States',
			lat: 58.18,
			lon: -134.24
		}, {
			city: 'Kansas City',
			prov: 'Mo.',
			country: 'United States',
			lat: 39.6,
			lon: -94.35
		}, {
			city: 'Key West',
			prov: 'Fla.',
			country: 'United States',
			lat: 24.33,
			lon: -81.48
		}, {
			city: 'Klamath Falls',
			prov: 'Ore.',
			country: 'United States',
			lat: 42.1,
			lon: -121.44
		}, {
			city: 'Knoxville',
			prov: 'Tenn.',
			country: 'United States',
			lat: 35.57,
			lon: -83.56
		}, {
			city: 'Las Vegas',
			prov: 'Nev.',
			country: 'United States',
			lat: 36.1,
			lon: -115.12
		}, {
			city: 'Lewiston',
			prov: 'Idaho',
			country: 'United States',
			lat: 46.24,
			lon: -117.2
		}, {
			city: 'Lincoln',
			prov: 'Neb.',
			country: 'United States',
			lat: 40.5,
			lon: -96.4
		}, {
			city: 'Long Beach',
			prov: 'Calif.',
			country: 'United States',
			lat: 33.46,
			lon: -118.11
		}, {
			city: 'Los Angeles',
			prov: 'Calif.',
			country: 'United States',
			lat: 34.3,
			lon: -118.15
		}, {
			city: 'Louisville',
			prov: 'Ky.',
			country: 'United States',
			lat: 38.15,
			lon: -85.46
		}, {
			city: 'Manchester',
			prov: 'N.H.',
			country: 'United States',
			lat: 43,
			lon: -71.3
		}, {
			city: 'Memphis',
			prov: 'Tenn.',
			country: 'United States',
			lat: 35.9,
			lon: -90.3
		}, {
			city: 'Miami',
			prov: 'Fla.',
			country: 'United States',
			lat: 25.46,
			lon: -80.12
		}, {
			city: 'Milwaukee',
			prov: 'Wis.',
			country: 'United States',
			lat: 43.2,
			lon: -87.55
		}, {
			city: 'Minneapolis',
			prov: 'Minn.',
			country: 'United States',
			lat: 44.59,
			lon: -93.14
		}, {
			city: 'Mobile',
			prov: 'Ala.',
			country: 'United States',
			lat: 30.42,
			lon: -88.3
		}, {
			city: 'Montgomery',
			prov: 'Ala.',
			country: 'United States',
			lat: 32.21,
			lon: -86.18
		}, {
			city: 'Montpelier',
			prov: 'Vt.',
			country: 'United States',
			lat: 44.15,
			lon: -72.32
		}, {
			city: 'Nashville',
			prov: 'Tenn.',
			country: 'United States',
			lat: 36.1,
			lon: -86.47
		}, {
			city: 'Newark',
			prov: 'N.J.',
			country: 'United States',
			lat: 40.44,
			lon: -74.1
		}, {
			city: 'New Haven',
			prov: 'Conn.',
			country: 'United States',
			lat: 41.19,
			lon: -72.55
		}, {
			city: 'New Orleans',
			prov: 'La.',
			country: 'United States',
			lat: 29.57,
			lon: -90.4
		}, {
			city: 'New York',
			prov: 'N.Y.',
			country: 'United States',
			lat: 40.47,
			lon: -73.58
		}, {
			city: 'Nome',
			prov: 'Alaska',
			country: 'United States',
			lat: 64.25,
			lon: -165.3
		}, {
			city: 'Oakland',
			prov: 'Calif.',
			country: 'United States',
			lat: 37.48,
			lon: -122.16
		}, {
			city: 'Oklahoma City',
			prov: 'Okla.',
			country: 'United States',
			lat: 35.26,
			lon: -97.28
		}, {
			city: 'Omaha',
			prov: 'Neb.',
			country: 'United States',
			lat: 41.15,
			lon: -95.56
		}, {
			city: 'Philadelphia',
			prov: 'Pa.',
			country: 'United States',
			lat: 39.57,
			lon: -75.1
		}, {
			city: 'Phoenix',
			prov: 'Ariz.',
			country: 'United States',
			lat: 33.29,
			lon: -112.4
		}, {
			city: 'Pierre',
			prov: 'S.D.',
			country: 'United States',
			lat: 44.22,
			lon: -100.21
		}, {
			city: 'Pittsburgh',
			prov: 'Pa.',
			country: 'United States',
			lat: 40.27,
			lon: -79.57
		}, {
			city: 'Portland',
			prov: 'Maine',
			country: 'United States',
			lat: 43.4,
			lon: -70.15
		}, {
			city: 'Portland',
			prov: 'Ore.',
			country: 'United States',
			lat: 45.31,
			lon: -122.41
		}, {
			city: 'Providence',
			prov: 'R.I.',
			country: 'United States',
			lat: 41.5,
			lon: -71.24
		}, {
			city: 'Raleigh',
			prov: 'N.C.',
			country: 'United States',
			lat: 35.46,
			lon: -78.39
		}, {
			city: 'Reno',
			prov: 'Nev.',
			country: 'United States',
			lat: 39.3,
			lon: -119.49
		}, {
			city: 'Richfield',
			prov: 'Utah',
			country: 'United States',
			lat: 38.46,
			lon: -112.5
		}, {
			city: 'Richmond',
			prov: 'Va.',
			country: 'United States',
			lat: 37.33,
			lon: -77.29
		}, {
			city: 'Roanoke',
			prov: 'Va.',
			country: 'United States',
			lat: 37.17,
			lon: -79.57
		}, {
			city: 'Sacramento',
			prov: 'Calif.',
			country: 'United States',
			lat: 38.35,
			lon: -121.3
		}, {
			city: 'St. Louis',
			prov: 'Mo.',
			country: 'United States',
			lat: 38.35,
			lon: -90.12
		}, {
			city: 'Salt Lake City',
			prov: 'Utah',
			country: 'United States',
			lat: 40.46,
			lon: -111.54
		}, {
			city: 'San Antonio',
			prov: 'Tex.',
			country: 'United States',
			lat: 29.23,
			lon: -98.33
		}, {
			city: 'San Diego',
			prov: 'Calif.',
			country: 'United States',
			lat: 32.42,
			lon: -117.1
		}, {
			city: 'San Francisco',
			prov: 'Calif.',
			country: 'United States',
			lat: 37.47,
			lon: -122.26
		}, {
			city: 'San Jose',
			prov: 'Calif.',
			country: 'United States',
			lat: 37.2,
			lon: -121.53
		}, {
			city: 'San Juan',
			prov: 'P.R.',
			country: 'United States',
			lat: 18.3,
			lon: -66.1
		}, {
			city: 'Santa Fe',
			prov: 'N.M.',
			country: 'United States',
			lat: 35.41,
			lon: -105.57
		}, {
			city: 'Savannah',
			prov: 'Ga.',
			country: 'United States',
			lat: 32.5,
			lon: -81.5
		}, {
			city: 'Seattle',
			prov: 'Wash.',
			country: 'United States',
			lat: 47.37,
			lon: -122.2
		}, {
			city: 'Shreveport',
			prov: 'La.',
			country: 'United States',
			lat: 32.28,
			lon: -93.42
		}, {
			city: 'Sioux Falls',
			prov: 'S.D.',
			country: 'United States',
			lat: 43.33,
			lon: -96.44
		}, {
			city: 'Sitka',
			prov: 'Alaska',
			country: 'United States',
			lat: 57.1,
			lon: -135.15
		}, {
			city: 'Spokane',
			prov: 'Wash.',
			country: 'United States',
			lat: 47.4,
			lon: -117.26
		}, {
			city: 'Springfield',
			prov: 'Ill.',
			country: 'United States',
			lat: 39.48,
			lon: -89.38
		}, {
			city: 'Springfield',
			prov: 'Mass.',
			country: 'United States',
			lat: 42.6,
			lon: -72.34
		}, {
			city: 'Springfield',
			prov: 'Mo.',
			country: 'United States',
			lat: 37.13,
			lon: -93.17
		}, {
			city: 'Syracuse',
			prov: 'N.Y.',
			country: 'United States',
			lat: 43.2,
			lon: -76.8
		}, {
			city: 'Tampa',
			prov: 'Fla.',
			country: 'United States',
			lat: 27.57,
			lon: -82.27
		}, {
			city: 'Toledo',
			prov: 'Ohio',
			country: 'United States',
			lat: 41.39,
			lon: -83.33
		}, {
			city: 'Tulsa',
			prov: 'Okla.',
			country: 'United States',
			lat: 36.9,
			lon: -95.59
		}, {
			city: 'Virginia Beach',
			prov: 'Va.',
			country: 'United States',
			lat: 36.51,
			lon: -75.58
		}, {
			city: 'Washington',
			prov: 'D.C.',
			country: 'United States',
			lat: 38.53,
			lon: -77.2
		}, {
			city: 'Wichita',
			prov: 'Kan.',
			country: 'United States',
			lat: 37.43,
			lon: -97.17
		}, {
			city: 'Wilmington',
			prov: 'N.C.',
			country: 'United States',
			lat: 34.14,
			lon: -77.57
		} ];
		
		
		
		// Canadian Cities
		var aCan = [ {
			city: 'Calgary',
			prov: 'Alba.',
			country: 'Canada',
			lat: 51.1,
			lon: -114.1
		}, {
			city: 'Edmonton',
			prov: 'Alb.',
			country: 'Canada',
			lat: 53.34,
			lon: -113.28
		}, {
			city: 'Kingston',
			prov: 'Ont.',
			country: 'Canada',
			lat: 44.15,
			lon: -76.3
		}, {
			city: 'London',
			prov: 'Ont.',
			country: 'Canada',
			lat: 43.2,
			lon: -81.34
		}, {
			city: 'Montreal',
			prov: 'Que.',
			country: 'Canada',
			lat: 45.3,
			lon: -73.35
		}, {
			city: 'Moose Jaw',
			prov: 'Sask.',
			country: 'Canada',
			lat: 50.37,
			lon: -105.31
		}, {
			city: 'Nelson',
			prov: 'B.C.',
			country: 'Canada',
			lat: 49.3,
			lon: -117.17
		}, {
			city: 'Ottawa',
			prov: 'Ont.',
			country: 'Canada',
			lat: 45.24,
			lon: -75.43
		}, {
			city: 'Quebec',
			prov: 'Que.',
			country: 'Canada',
			lat: 46.49,
			lon: -71.11
		}, {
			city: 'St. John',
			prov: 'N.B.',
			country: 'Canada',
			lat: 45.18,
			lon: -66.1
		}, {
			city: 'Toronto',
			prov: 'Ont.',
			country: 'Canada',
			lat: 43.4,
			lon: -79.24
		}, {
			city: 'Vancouver',
			prov: 'B.C.',
			country: 'Canada',
			lat: 49.13,
			lon: -123.6
		}, {
			city: 'Victoria',
			prov: 'B.C.',
			country: 'Canada',
			lat: 48.25,
			lon: -123.21
		}, {
			city: 'Winnipeg',
			prov: 'Man.',
			country: 'Canada',
			lat: 49.54,
			lon: -97.7
		} ];
		
		
		var bShuffle = false;
		
		//
		var aArgs = [];
		for ( var i = 0; i < arguments.length; i++ ) {
			
			var sArg = $.trim( arguments[ i ] );
			
			sArg = sArg.toLowerCase();
			
			if ( -1 != $.inArray( sArg, [ 'rand', 'random', 'shuffle' ] ) ) {
				bShuffle = true;
			} else {
				if ( sArg ) aArgs.push( sArg );
			}
		}
		
		
		////
		var aOut = [];
		
		if (
			( 0 == aArgs.length ) || 
			( -1 != $.inArray( 'world', aArgs ) )
		) {
			aOut = aOut.concat( aWorld );
		}
		
		if (
			( 0 == aArgs.length ) || 
			( -1 != $.inArray( 'us', aArgs ) ) || 
			( -1 != $.inArray( 'united states', aArgs ) )
		) {
			aOut = aOut.concat( aUs );
		}

		if (
			( 0 == aArgs.length ) || 
			( -1 != $.inArray( 'can', aArgs ) ) || 
			( -1 != $.inArray( 'canada', aArgs ) )
		) {
			aOut = aOut.concat( aCan );
		}
		
		
		//
		if ( bShuffle ) aOut.shuffle();
		
		return aOut;
		
	};
	
} )( jQuery );