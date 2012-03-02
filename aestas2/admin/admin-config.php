<?php

$admin_config = new stdClass;


// CMS name in title
$admin_config->title_cms = 'aestas';

// Seperator between title parts
$admin_config->title_seperator = ' // ';


// Form charset
$admin_config->charset = 'utf-8';


// Navigation structure
// include->css: screen.css will always be included
// include->javascript: jQuery will always be included
$admin_config->navigation = array(

	'Dashboard' => array(
		'link' => 'dashboard',
		'css_class' => 'dashboard',
		'include' => array(
			'css' => array( 'dashboard.css' ),
			'javascript' => array()
		),
		'sub_nav' => array(
			'Dashboard' => array(
				'link' => 'dashboard',
				'css_class' => 'dash'
			),
			'Statistics' => array(
				'link' => 'statistics',
				'css_class' => 'stats'
			),
			'Blogroll' => array(
				'link' => 'blogroll',
				'css_class' => 'blogroll'
			)
		)
	),

	'Create' => array(
		'link' => 'create',
		'css_class' => 'create',
		'include' => array(
			'css' => array( 'create.css' ),
			'javascript' => array( 'jquery.indent-1.0.min.js' )
		),
		'sub_nav' => array(
			'Add Post' => array(
				'link' => 'post',
				'css_class' => 'post'
			),
			'Add Page' => array(
				'link' => 'page',
				'css_class' => 'page'
			),
			'Add Category' => array(
				'link' => 'category',
				'css_class' => 'category'
			),
			'Add User' => array(
				'link' => 'user',
				'css_class' => 'user'
			)
		)
	),

	'Manage' => array(
		'link' => 'manage',
		'css_class' => 'manage',
		'include' => array(
			'css' => array( 'manage.css' ),
			'javascript' => array( 'jquery.indent-1.0.min.js' )
		),
		'sub_nav' => array(
			'Comments' => array(
				'link' => 'comments',
				'css_class' => 'comments'
			),
			'Posts' => array(
				'link' => 'posts',
				'css_class' => 'posts'
			),
			'Categories' => array(
				'link' => 'categories',
				'css_class' => 'categories'
			),
			'Pages' => array(
				'link' => 'pages',
				'css_class' => 'pages'
			),
			'Users' => array(
				'link' => 'users',
				'css_class' => 'users'
			)
		)
	),

	'Media' => array(
		'link' => 'media',
		'css_class' => 'media',
		'include' => array(
			'css' => array( 'media.css' ),
			'javascript' => array()
		),
		'sub_nav' => array(
			'Library' => array(
				'link' => 'library',
				'css_class' => 'library'
			),
			'Upload' => array(
				'link' => 'upload',
				'css_class' => 'upload'
			)
		)
	),

	'Theme' => array(
		'link' => 'theme',
		'css_class' => 'theme',
		'include' => array(
			'css' => array( 'theme.css' ),
			'javascript' => array()
		),
		'sub_nav' => array(
			'Choose Theme' => array(
				'link' => 'choose',
				'css_class' => 'choose'
			),
			'Upload Theme' => array(
				'link' => 'upload',
				'css_class' => 'upload'
			)
		)
	),

	'Settings' => array(
		'link' => 'settings',
		'css_class' => 'settings',
		'include' => array(
			'css' => array( 'settings.css' ),
			'javascript' => array()
		),
		'sub_nav' => array(
			'General' => array(
				'link' => 'general',
				'css_class' => 'general'
			),
			'Discussion' => array(
				'link' => 'discussion',
				'css_class' => 'discussion'
			),
			'Newsfeed' => array(
				'link' => 'newsfeed',
				'css_class' => 'newsfeed'
			),
			'Permalinks' => array(
				'link' => 'permalinks',
				'css_class' => 'permalinks'
			),
			'Rules' => array(
				'link' => 'rules',
				'css_class' => 'rules'
			),
			'Database' => array(
				'link' => 'database',
				'css_class' => 'database'
			),
		)
	)

);
