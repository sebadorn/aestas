<?php


class ae_Misc {


	/**
	 * @param integer $select What month to pre-select.
	 * @return string Option-tags with all months and the current with selected-attribute.
	 */
	public static function MonthsForSelect( $select = 0 ) {
		$compare = ( $select > 0 ) ? $select : date( 'm' );

		$out = '';
		for( $i = 1; $i <= 12; $i++ ) {
			$month = ( $i < 10 ) ? '0' . $i : $i;
			$selected = ( $i == $compare ) ? ' selected="selected"' : '';
			$out .= '<option value="' . $month . '"' . $selected . '>'
				. date( 'M', mktime( 0, 0, 0, $i + 1, 0, 0 ) ) . '</option>' . "\n";
		}
		return $out;
	}


	public static function UsersForSelect( $select = 0 ) {
		$query = ae_Database::Query( '
			SELECT
				user_id,
				user_name
			FROM `' . TABLE_USERS . '`
			WHERE user_status != "deleted"
			AND user_status != "trash"
			ORDER BY
				user_name ASC,
				user_id ASC
		' );

		$out = '';
		while( $user = mysql_fetch_assoc( $query ) ) {
			$selected = ( $select == $user['user_id'] ) ? ' selected="selected"' : '';
			$out .= '<option value="' . $user['user_id'] . '"' . $selected .  '>'
				. $user['user_name'] . ' (ID: ' . $user['user_id'] . ')</option>' . PHP_EOL;
		}

		return $out;
	}


	/**
	 * @param string $type radio or checkbox
	 * @param int $not_id Don't list this category
	 * @param int $check_id Check this category/categories
	 * @return string Categories in a structured list
	 */
	public static function ListCategories( $type, $not_id = array( '0' ), $check_id = 0 ) {
		$not_in = implode( ', ', $not_id );

		$query = ae_Database::Query( '
			SELECT
				cat_id,
				cat_name
			FROM `' . TABLE_CATEGORIES . '`
			WHERE cat_id NOT IN ( ' . $not_in . ' )
			AND cat_id != 1
			AND ( cat_parent IS NULL OR cat_parent = 0 )
			ORDER BY cat_name ASC
		' );

		$name = ( $type == 'radio' ) ? 'cat' : 'cats[]';
		$out = '';

		while( $c = mysql_fetch_assoc( $query ) ) {
			if( is_array( $check_id ) ) {
				$check = in_array( $c['cat_id'], $check_id ) ? ' checked="checked"' : '';
			}
			else {
				$check = ( $c['cat_id'] == $check_id ) ? ' checked="checked"' : '';
			}

			$out .= '<li>';
			$out .= '<input id="cat-' . $c['cat_id'] . '" name="' . $name . '"'
					. ' type="' . $type . '" value="' . $c['cat_id'] . '" ' . $check . '/>' . PHP_EOL;
			$out .= '<label for="cat-' . $c['cat_id'] . '">' . $c['cat_name'] . '</label>' . PHP_EOL;
			$out .= self::ListSubCategories( $type, $c['cat_id'], $not_id, $check_id );
			$out .= '</li>' . PHP_EOL;
		}

		if( $out == '' ) {
			$cat = ae_Category::getCategoryById( 1 );
			$out = '<li>';
			$out .= '<input type="' . $type . '" readonly="readonly" checked="checked" />';
			$out .= '<label>' . $cat->getNameHTML() . '</label>';
			$out .= '</li>' . PHP_EOL;
		}

		return $out;
	}


	/**
	 * @see get_categories
	 * @param $type radio or checkbox
	 * @param $cat_id Categorie whose sub-categories we want
	 * @param $not_id Don't list this category
	 * @param $check_id Check this category/categories
	 * @return All sub-categories in a structured list
	 */
	protected static function ListSubCategories( $type, $cat_id, $not_id = array( '0' ), $check_id = 0 ) {
		$not_in = implode( ', ', $not_id );
		$list_open = true;
		$i = 0;

		$query = ae_Database::Query( '
			SELECT
				COUNT( cat_id ) AS count,
				cat_id,
				cat_name
			FROM `' . TABLE_CATEGORIES . '`
			WHERE cat_id NOT IN ( ' . $not_in . ' )
			AND cat_id != 1
			AND cat_parent = ' . mysql_real_escape_string( $cat_id ) . '
			GROUP BY cat_id, cat_name
			ORDER BY cat_name ASC
		' );

		$name = ( $type == 'radio' ) ? 'cat' : 'cats[]';

		$out = '';
		while( $sc = mysql_fetch_assoc( $query ) ) {
			// No sub-categories means we can return
			if( $sc['count'] == 0 ) {
				return;
			}

			// Start a new list
			if( $list_open ) {
				$out = '<ul>';
				$list_open = false;
			}

			// Output of sub-categories with recursive call for potential sub-categories of the sub-categories
			if( is_array( $check_id ) ) {
				$check = in_array( $sc['cat_id'], $check_id ) ? ' checked="checked"' : '';
			}
			else {
				$check = ( $sc['cat_id'] == $check_id ) ? ' checked="checked"' : '';
			}

			$out .= '<li>' . PHP_EOL
				. '<input id="cat-' . $sc['cat_id'] . '" name="' . $name . '"'
				. ' type="' . $type . '" value="' . $sc['cat_id'] . '" ' . $check . '/>'
				. '<label for="cat-' . $sc['cat_id'] . '">' . $sc['cat_name'] . '</label>'
				. self::ListSubCategories( $type, $sc['cat_id'], $not_id, $check_id ) . PHP_EOL
				. '</li>' . PHP_EOL;

			// Close the list if we are through
			if( $sc['count'] == ++$i ) {
				$out .= '</ul>';
				break;
			}
		}

		return $out;
	}


	public static function PagesForSelect( $id = 0 ) {
		$pages_sql = ae_Database::Query( '
			SELECT
				post_id,
				post_title
			FROM `' . TABLE_POSTS . '`
			WHERE post_list_page IS NOT NULL
			ORDER BY
				post_title ASC,
				post_id ASC
		' );

		$out = '';
		while( $page = mysql_fetch_assoc( $pages_sql ) ) {
			$select = ( $page['post_id'] == $id ) ? ' selected="selected"' : '';
			$out .= '<option value="' . $page['post_id'] . '"' . $select . '>';
			$out .= $page['post_title'];
			$out .= '</option>' . PHP_EOL;
		}
		return $out;
	}


	public static function ProcessTags2String( $tags, $tags_js = array() ) {
		if( is_string( $tags ) ) {
			$tags = explode( ';', $tags );
		}
		if( !empty( $tags_js ) ) {
			$tags = array_merge( $tags, $tags_js );
		}
		$tags = array_map( 'trim', $tags );
		$tags = array_unique( $tags );
		sort( $tags );

		$tags_out = '';
		foreach( $tags as $tag ) {
			if( !empty( $tag ) ) {
				$tags_out .= $tag . ';';
			}
		}
		return $tags_out;
	}


	public static function SizeLimit() {
		$replace_this = array( 'M', 'K', 'G' );
		$with_this = array( ' MByte', ' KByte', ' GByte' );

		$size = str_replace( $replace_this, $with_this, ini_get( 'upload_max_filesize' ) );

		return $size;
	}


	/**
	 * @global boolean $sel Flag if the former timezone was already selected.
	 * @global string $timezone Timezone to show as selected option.
	 * @param string $name Written name of timezone.
	 * @param array $list List with all timezones for a continent.
	 * @return XHTML
	 */
	protected static function TimezoneOptGroup( $name, $list, $timezone ) {
		$out = '<optgroup label="' . $name . '">' . "\n";
		foreach( $list as $key => $val ) {
			if( !is_int( $key ) ) {
				$text = $key;
			}
			else {
				$text = str_replace( '/', ' – ', $val );
				$text = str_replace( '_', ' ', $text );
			}

			if( $name . '/' . $val == $timezone ) {
				$out .= '<option value="' . $name . '/' . $val . '" selected="selected">';
				$out .= $text . '</option>' . PHP_EOL;
			}
			else {
				$out .= '<option value="' . $name . '/' . $val . '">';
				$out .= $text . '</option>' . PHP_EOL;
			}

		}
		$out .= '</optgroup>' . PHP_EOL;
		return $out;
	}


	public static function Timezones() {
		$timezone = ae_Settings::Timezone();
		$out = '';


		$africa = array(
			'Abidjan', 'Accra', 'Addis_Ababa', 'Algiers', 'Asmara',
			'Asmera', 'Bamako', 'Bangui', 'Banjul', 'Bissau',
			'Blantyre', 'Brazzaville', 'Bujumbura', 'Cairo', 'Casablanca',
			'Ceuta', 'Conakry', 'Dakar', 'Dar_es_Salaam', 'Djibouti',
			'Douala', 'El_Aaiun', 'Freetown', 'Gaborone', 'Harare',
			'Johannesburg', 'Kampala', 'Khartoum', 'Kigali', 'Kinshasa',
			'Lagos', 'Libreville', 'Lome', 'Luanda', 'Lubumbashi',
			'Lusaka', 'Malabo', 'Maputo', 'Maseru', 'Mbabane',
			'Mogadishu', 'Monrovia', 'Nairobi', 'Ndjamena', 'Niamey',
			'Nouakchott', 'Ouagadougou', 'Porto-Novo', 'Sao_Tome', 'Timbuktu',
			'Tripoli', 'Tunis', 'Windhoek'
		);

		$out .= self::TimezoneOptGroup( 'Africa', $africa, $timezone );
		unset( $africa );

		$america = array(
			'Adak', 'Anchorage', 'Anguilla', 'Antigua', 'Araguaina',
			'Argentina/Buenos_Aires', 'Argentina/Catamarca',
			'Argentina – Comodoro Rivadavia' => 'Argentina/ComodRivadavia', 'Argentina/Cordoba',
			'Argentina/Jujuy', 'Argentina/La_Rioja', 'Argentina/Mendoza', 'Argentina/Rio_Gallegos',
			'Argentina/Salta', 'Argentina/San_Juan', 'Argentina/San_Luis', 'Argentina/Tucuman',
			'Argentina/Ushuaia', 'Aruba', 'Asuncion', 'Atikokan', 'Atka', 'Bahia', 'Barbados', 'Belem',
			'Belize', 'Blanc-Sablon', 'Boa_Vista', 'Bogota', 'Boise',
			'Buenos_Aires', 'Cambridge_Bay', 'Campo_Grande', 'Cancun', 'Caracas',
			'Catamarca', 'Cayenne', 'Cayman', 'Chicago', 'Chihuahua',
			'Coral_Harbour', 'Cordoba', 'Costa_Rica', 'Cuiaba', 'Curacao',
			'Danmarkshavn', 'Dawson', 'Dawson_Creek', 'Denver', 'Detroit',
			'Dominica', 'Edmonton', 'Eirunepe', 'El_Salvador', 'Ensenada',
			'Fort_Wayne', 'Fortaleza', 'Glace_Bay', 'Godthab', 'Goose_Bay',
			'Grand_Turk', 'Grenada', 'Guadeloupe', 'Guatemala', 'Guayaquil',
			'Guyana', 'Halifax', 'Havana', 'Hermosillo', 'Indiana/Indianapolis',
			'Indiana/Knox', 'Indiana/Marengo', 'Indiana/Petersburg', 'Indiana/Tell_City', 'Indiana/Vevay',
			'Indiana/Vincennes', 'Indiana/Winamac', 'Indianapolis', 'Inuvik', 'Iqaluit',
			'Jamaica', 'Jujuy', 'Juneau', 'Kentucky/Louisville', 'Kentucky/Monticello',
			'Knox_IN', 'La_Paz', 'Lima', 'Los_Angeles', 'Louisville',
			'Maceio', 'Managua', 'Manaus', 'Marigot', 'Martinique',
			'Mazatlan', 'Mendoza', 'Menominee', 'Merida', 'Mexico_City',
			'Miquelon', 'Moncton', 'Monterrey', 'Montevideo', 'Montreal',
			'Montserrat', 'Nassau', 'New_York', 'Nipigon', 'Nome',
			'Noronha', 'North_Dakota/Center', 'North_Dakota/New_Salem', 'Panama', 'Pangnirtung',
			'Paramaribo', 'Phoenix', 'Port-au-Prince', 'Port_of_Spain', 'Porto_Acre',
			'Porto_Velho', 'Puerto_Rico', 'Rainy_River', 'Rankin_Inlet', 'Recife',
			'Regina', 'Resolute', 'Rio_Branco', 'Rosario', 'Santarem',
			'Santiago', 'Santo_Domingo', 'Sao_Paulo', 'Scoresbysund', 'Shiprock',
			'St_Barthelemy', 'St_Johns', 'St_Kitts', 'St_Lucia', 'St_Thomas',
			'St_Vincent', 'Swift_Current', 'Tegucigalpa', 'Thule', 'Thunder_Bay',
			'Tijuana', 'Toronto', 'Tortola', 'Vancouver', 'Virgin',
			'Whitehorse', 'Winnipeg', 'Yakutat', 'Yellowknife'
		);

		$out .= self::TimezoneOptGroup( 'America', $america, $timezone );
		unset( $america );


		$antarctica = array(
			'Casey', 'Davis', 'Dumont d’Urville' => 'DumontDUrville',
			'Mawson', 'McMurdo', 'Palmer', 'Rothera', 'South_Pole', 'Syowa', 'Vostok'
		);

		$out .= self::TimezoneOptGroup( 'Antarctica', $antarctica, $timezone );
		unset( $antarctica );


		$arctic = array( 'Longyearbyen' );

		$out .= self::TimezoneOptGroup( 'Arctic', $arctic, $timezone );
		unset( $arctic );


		$asia = array(
			'Aden', 'Almaty', 'Amman', 'Anadyr', 'Aqtau',
			'Aqtobe', 'Ashgabat', 'Ashkhabad', 'Baghdad', 'Bahrain',
			'Baku', 'Bangkok', 'Beirut', 'Bishkek', 'Brunei',
			'Calcutta', 'Choibalsan', 'Chongqing', 'Chungking', 'Colombo',
			'Dacca', 'Damascus', 'Dhaka', 'Dili', 'Dubai',
			'Dushanbe', 'Gaza', 'Harbin', 'Ho_Chi_Minh',
			'Hong_Kong', 'Hovd', 'Irkutsk', 'Istanbul', 'Jakarta',
			'Jayapura', 'Jerusalem', 'Kabul', 'Kamchatka', 'Karachi', 'Kashgar',
			'Kathmandu', 'Katmandu', 'Kolkata', 'Krasnoyarsk',
			'Kuala Lumpur' => 'Kuala_Lumpur', 'Kuching', 'Kuwait', 'Macao',
			'Macau', 'Magadan', 'Makassar', 'Manila', 'Muscat', 'Nicosia',
			'Novokuznetsk', 'Novosibirsk', 'Omsk', 'Oral', 'Phnom_Penh',
			'Pontianak', 'Pyongyang', 'Qatar', 'Qyzylorda', 'Rangoon', 'Riyadh',
			'Saigon', 'Sakhalin', 'Samarkand', 'Seoul', 'Shanghai',
			'Singapore', 'Taipei', 'Tashkent', 'Tbilisi', 'Tehran',
			'Tel_Aviv', 'Thimbu', 'Thimphu', 'Tokyo',
			'Ujung_Pandang', 'Ulaanbaatar', 'Ulan_Bator',
			'Urumqi', 'Vientiane', 'Vladivostok', 'Yakutsk', 'Yekaterinburg', 'Yerevan'
		);

		$out .= self::TimezoneOptGroup( 'Asia', $asia, $timezone );
		unset( $asia );


		$atlantic = array(
			'Azores', 'Bermuda', 'Canary', 'Cape_Verde',
			'Faeroe', 'Faroe', 'Jan_Mayen', 'Madeira', 'Reykjavik',
			'South_Georgia', 'St. Helena' => 'St_Helena', 'Stanley'
		);

		$out .= self::TimezoneOptGroup( 'Atlantic', $atlantic, $timezone );
		unset( $atlantic );


		$australia = array(
			'ACT', 'Adelaide', 'Brisbane', 'Broken_Hill',
			'Canberra', 'Currie', 'Darwin', 'Eucla', 'Hobart', 'LHI', 'Lindeman',
			'Lord_Howe', 'Melbourne', 'North', 'NSW', 'Perth', 'Queensland', 'South',
			'Sydney', 'Tasmania', 'Victoria', 'West', 'Yancowinna'
		);

		$out .= self::TimezoneOptGroup( 'Australia', $australia, $timezone );
		unset( $australia );


		$europe = array(
			'Amsterdam', 'Andorra', 'Athens', 'Belfast', 'Belgrade',
			'Berlin', 'Bratislava', 'Brussels', 'Bucharest', 'Budapest',
			'Chisinau', 'Copenhagen', 'Dublin', 'Gibraltar', 'Guernsey',
			'Helsinki', 'Isle_of_Man', 'Istanbul', 'Jersey', 'Kaliningrad',
			'Kiev', 'Lisbon', 'Ljubljana', 'London', 'Luxembourg',
			'Madrid', 'Malta', 'Mariehamn', 'Minsk', 'Monaco',
			'Moscow', 'Nicosia', 'Oslo', 'Paris', 'Podgorica',
			'Prague', 'Riga', 'Rome', 'Samara', 'San_Marino',
			'Sarajevo', 'Simferopol', 'Skopje', 'Sofia', 'Stockholm',
			'Tallinn', 'Tirane', 'Tiraspol', 'Uzhgorod', 'Vaduz',
			'Vatican', 'Vienna', 'Vilnius', 'Volgograd', 'Warsaw',
			'Zagreb', 'Zaporozhye', 'Zurich'
		);

		$out .= self::TimezoneOptGroup( 'Europe', $europe, $timezone );
		unset( $europe );


		$indian = array(
			'Antananarivo', 'Chagos', 'Christmas', 'Cocos', 'Comoro',
			'Kerguelen', 'Mahe', 'Maldives', 'Mauritius', 'Mayotte', 'Reunion'
		);

		$out .= self::TimezoneOptGroup( 'Indian', $indian, $timezone );
		unset( $indian );


		$pacific = array(
			'Apia', 'Auckland', 'Chatham', 'Easter', 'Efate',
			'Enderbury', 'Fakaofo', 'Fiji', 'Funafuti', 'Galapagos',
			'Gambier', 'Guadalcanal', 'Guam', 'Honolulu', 'Johnston',
			'Kiritimati', 'Kosrae', 'Kwajalein', 'Majuro', 'Marquesas',
			'Midway', 'Nauru', 'Niue', 'Norfolk', 'Noumea',
			'Pago_Pago', 'Palau', 'Pitcairn', 'Ponape', 'Port_Moresby',
			'Rarotonga', 'Saipan', 'Samoa', 'Tahiti', 'Tarawa',
			'Tongatapu', 'Truk', 'Wake', 'Wallis', 'Yap'
		);

		$out .= self::TimezoneOptGroup( 'Pacific', $pacific, $timezone);
		unset( $pacific );


		$etc = array(
			'UTC -12' => 'GMT+12', 'UTC -11' => 'GMT+11', 'UTC -10' => 'GMT+10',
			'UTC -9' => 'GMT+9', 'UTC -8' => 'GMT+8', 'UTC -7' => 'GMT+7',
			'UTC -6' => 'GMT+6', 'UTC -5' => 'GMT+5', 'UTC -4' => 'GMT+4', 'UTC -3' => 'GMT+3',
			'UTC -2' => 'GMT+2', 'UTC -1' => 'GMT+1',
			'UTC' => 'UTC',
			'UTC +1' => 'GMT-1', 'UTC +2' => 'GMT-2', 'UTC +3' => 'GMT-3', 'UTC +4' => 'GMT-4',
			'UTC +5' => 'GMT-5', 'UTC +6' => 'GMT-6', 'UTC +7' => 'GMT-7', 'UTC +8' => 'GMT-8',
			'UTC +9' => 'GMT-9', 'UTC +10' => 'GMT-10', 'UTC +11' => 'GMT-11', 'UTC +12' => 'GMT-12',
			'UTC +13' => 'GMT-13', 'UTC +14' => 'GMT-14'
		);

		$out .= '<optgroup label="Manual offsets">' . PHP_EOL;
		foreach( $etc as $key => $et ) {
			if( strlen( $key ) > 3 ) {
				$key .= ':00';
			}
			if( $timezone == 'Etc/' . $et ) {
				$out .= '<option value="Etc/' . $et . '" selected="selected">';
				$out .= $key . '</option>' . PHP_EOL;
			}
			else {
				$out .= '<option value="Etc/' . $et . '">' . $key . '</option>' . PHP_EOL;
			}
		}
		$out .= '</optgroup>' . PHP_EOL;
		unset( $etc );

		return $out;
	}


}