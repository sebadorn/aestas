<?php


class ae_Newsfeed {

	protected $title = '';
	protected $description = '';
	protected $link = array(
		'blog' => '',
		'feed' => ''
	);
	protected $image = array(
		'url' => '',
		'title' => '',
		'link' => ''
	);

	protected $items = array();

	protected $rss_attr = array(
		'xmlns:atom' => 'http://www.w3.org/2005/Atom',
		'xmlns:dc' => 'http://purl.org/dc/elements/1.1/',
		'xmlns:content' => 'http://purl.org/rss/1.0/modules/content/',
		'xmlns:slash' => 'http://purl.org/rss/1.0/modules/slash/'
	);


	/**
	 * Generates the feed.
	 */
	public function generate() {
		$out = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
		$out .= $this->generate_element_rss();
		return $out;
	}


	public function setTitle( $title ) {
		$this->title = $title;
	}


	public function setDescription( $description ) {
		$this->description = $description;
	}


	public function setBlogLink( $link ) {
		$this->link['blog'] = $link;
	}


	public function setFeedLink( $link ) {
		$this->link['feed'] = $link;
	}


	public function setImage( $url, $title, $link ) {
		$this->image['url'] = $url;
		$this->image['title'] = $title;
		$this->image['link'] = $link;
	}


	public function add_item( $item ) {
		$this->items[] = $item;
	}



	//---------- protected functions


	protected function generate_element_rss() {
		$rss = '<rss version="2.0"';
		foreach( $this->rss_attr as $attr => $value ) {
			$rss .= ' ' . $attr . '="' . htmlspecialchars( $value ) . '"';
		}
		$rss .= '>' . PHP_EOL;

		$rss .= $this->generate_element_channel();
		$rss .= '</rss>';

		return $rss;
	}


	protected function generate_element_channel() {
		$t1 = ae_Formatter::Tabs( 1 );
		$t2 = ae_Formatter::Tabs( 2 );

		$channel = $t1 . '<channel>' . PHP_EOL;

		$channel .= $t2 . '<title>' . htmlspecialchars( $this->title ) . '</title>' . PHP_EOL;
		$channel .= $t2 . '<description>' . htmlspecialchars( $this->description ) . '</description>' . PHP_EOL;
		$channel .= $this->generate_element_image();
		$channel .= $t2 . '<pubDate>' . date( DATE_RFC1123, time() ) . '</pubDate>' . PHP_EOL;
		$channel .= $t2 . '<lastBuildDate>' . date( DATE_RFC1123, time() ) . '</lastBuildDate>' . PHP_EOL;
		$channel .= $t2 . '<link>' . htmlspecialchars( $this->link['blog'] ) . '</link>' . PHP_EOL;
		$channel .= $t2 . '<atom:link rel="self" type="application/rss+xml" href="'
				. htmlspecialchars( $this->link['feed'] ) . '"/>' . PHP_EOL;
		$channel .= $this->generate_element_items();
		$channel .= $t1 . '</channel>' . PHP_EOL;

		return $channel;
	}


	protected function generate_element_image() {
		if( $this->image['url'] == '' ) {
			return '';
		}

		$t2 = ae_Formatter::Tabs( 2 );
		$t3 = ae_Formatter::Tabs( 3 );

		$image = $t2 . '<image>' . PHP_EOL;
		$image .= $t3 . '<url>' . $this->image['url'] . '</url>' . PHP_EOL;
		$image .= $t3 . '<title>' . $this->image['title'] . '</title>' . PHP_EOL;
		$image .= $t3 . '<link>' . $this->image['link'] . '</link>' . PHP_EOL;
		$image .= $t2 . '</image>' . PHP_EOL;

		return $image;
	}


	protected function generate_element_items() {
		$t2 = ae_Formatter::Tabs( 2 );
		$t3 = ae_Formatter::Tabs( 3 );
		$items = '';

		foreach( $this->items as $item ) {
			$items .= $t2 . '<item>' . PHP_EOL;
			$items .= $t3 . '<title>' . self::EncodeForFeed( $item['title'] ) . '</title>' . PHP_EOL;
			$items .= $t3 . '<pubDate>' . date( DATE_RFC1123, $item['date_created'] ) . '</pubDate>' . PHP_EOL;
			$items .= $t3 . '<link>' . htmlspecialchars( $item['link'] ) . '</link>' . PHP_EOL;
			$items .= $t3 . '<guid>' . htmlspecialchars( $item['link'] ) . '</guid>' . PHP_EOL;

			if( isset( $item['comment_link'] ) ) {
				$items .= $t3 . '<comments>' . htmlspecialchars( $item['comment_link'] ) . '</comments>' . PHP_EOL;
			}

			if( isset( $item['categories'] ) ) {
				foreach( $item['categories'] as $category ) {
					$items .= $t3 . '<category>' . self::EncodeForFeed( $category ) . '</category>' . PHP_EOL;
				}
			}
			$items .= $t3 . '<dc:creator>' . self::EncodeForFeed( $item['author'] ) . '</dc:creator>' . PHP_EOL;
			$items .= $t3 . '<description>' . $this->generate_item_description( $item ) . '</description>' . PHP_EOL;
			$items .= $t3 . '<content:encoded>' . self::EncodeForFeed( $item['content'] ) . '</content:encoded>' . PHP_EOL;

			if( isset( $item['comments'] ) ) {
				$items .= $t3 . '<slash:comments>' . $item['comments'] . '</slash:comments>' . PHP_EOL;
			}

			$items .= $t2 . '</item>' . PHP_EOL;
		}

		return $items;
	}


	protected function generate_item_description( $item ) {
		$item['content'] = strip_tags( $item['content'] );
		if( strlen( $item['content'] ) > 255 ) {
			$item['content'] = substr( $item['content'], 0, 255 ) . '…';
		}

		return self::EncodeForFeed( $item['content'] );
	}



	//---------- static functions


	public static function EncodeForFeed( $string ) {
		if( strpos( $string, ']]>' ) !== false ) {
			$string = htmlspecialchars( $string );
		}
		else {
			$string = '<![CDATA[' . $string . ']]>';
		}
		return $string;
	}


}
