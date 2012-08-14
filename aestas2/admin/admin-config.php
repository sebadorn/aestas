<?php

$admin_config = new stdClass;

// CMS name in title
$admin_config->title_cms = 'aestas';

// Seperator between title parts
$admin_config->title_seperator = ' / ';

// Form charset
$admin_config->charset = 'utf-8';

// Navigation structure
$admin_config->navigation = array(
	'Dash' => array(
		'link' => 'dashboard',
		'css_class' => 'nav-link-dash',

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
		'css_class' => 'nav-link-create',

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
		'css_class' => 'nav-link-manage',

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
		'css_class' => 'nav-link-media',

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
		'css_class' => 'nav-link-theme',

		'sub_nav' => array(
			'Choose' => array(
				'link' => 'choose',
				'css_class' => 'choose'
			),
			'Upload' => array(
				'link' => 'upload',
				'css_class' => 'upload'
			)
		)
	),

	'Settings' => array(
		'link' => 'settings',
		'css_class' => 'nav-link-settings',

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