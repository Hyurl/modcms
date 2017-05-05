<?php
return array (
  'user' => 
  array (
    'user_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'user_name' => 'VARCHAR(255) DEFAULT NULL',
    'user_nickname' => 'VARCHAR(255) DEFAULT NULL',
    'user_realname' => 'VARCHAR(255) DEFAULT NULL',
    'user_password' => 'VARCHAR(255) DEFAULT NULL',
    'user_gender' => 'VARCHAR(255) DEFAULT NULL',
    'user_avatar' => 'VARCHAR(255) DEFAULT NULL',
    'user_identity' => 'VARCHAR(255) DEFAULT NULL',
    'user_company' => 'VARCHAR(255) DEFAULT NULL',
    'user_number' => 'VARCHAR(255) DEFAULT NULL',
    'user_email' => 'VARCHAR(255) DEFAULT NULL',
    'user_tel' => 'VARCHAR(255) DEFAULT NULL',
    'user_desc' => 'VARCHAR(255) DEFAULT NULL',
    'user_protect' => 'VARCHAR(255) DEFAULT NULL',
    'user_level' => 'INTEGER DEFAULT 1',
  ),
  'category' => 
  array (
    'category_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'category_name' => 'VARCHAR(255) DEFAULT NULL',
    'category_alias' => 'VARCHAR(255) DEFAULT NULL',
    'category_desc' => 'VARCHAR(255) DEFAULT NULL',
    'category_parent' => 'INTEGER DEFAULT 0',
    'category_posts' => 'INTEGER DEFAULT 0',
    'category_children' => 'INTEGER DEFAULT 0',
  ),
  'file' => 
  array (
    'file_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'file_name' => 'VARCHAR(255) DEFAULT NULL',
    'file_src' => 'VARCHAR(255) DEFAULT NULL',
    'file_desc' => 'VARCHAR(255) DEFAULT NULL',
    'file_time' => 'INTEGER DEFAULT 0',
    'user_id' => 'INTEGER DEFAULT 0',
  ),
  'post' => 
  array (
    'post_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'post_title' => 'VARCHAR(255) DEFAULT NULL',
    'post_content' => 'VARCHAR(16383) DEFAULT NULL',
    'post_thumbnail' => 'VARCHAR(255) DEFAULT NULL',
    'post_commentable' => 'INTEGER DEFAULT 0',
    'post_comments' => 'INTEGER DEFAULT 0',
    'post_time' => 'INTEGER DEFAULT 0',
    'post_link' => 'VARCHAR(255) DEFAULT NULL',
    'user_id' => 'INTEGER DEFAULT 0',
    'category_id' => 'INTEGER DEFAULT 0',
    'post_type' => 'INTEGER DEFAULT 0', //文章类型，0(默认)，1(短文), 2(页面)，3(草稿)
    'post_original' => 'INTEGER DEFAULT 0',  //是否原创
    'post_tags' => 'VARCHAR(255) DEFAULT NULL', //文章标签
  ),
  'comment' => 
  array (
    'comment_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'comment_content' => 'VARCHAR(1023) DEFAULT NULL',
    'comment_time' => 'INTEGER DEFAULT 0',
    'comment_parent' => 'INTEGER DEFAULT 0',
    'comment_parent_uid' => 'INTEGER DEFAULT 0',
    'comment_replies' => 'INTEGER DEFAULT 0',
    'comment_status' => 'INTEGER DEFAULT 0', //评论状态，0 待审核, 1 已发布
    'comment_ip' => 'VARCHAR(255) DEFAULT NULL',
    'post_id' => 'INTEGER DEFAULT 0',
    'user_id' => 'INTEGER DEFAULT 0',
  ),
  'blacklist' => 
  array (
    'blacklist_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'blacklist_uid' => 'INTEGER DEFAULT 0',
    'blacklist_email' => 'VARCHAR(255) DEFAULT NULL',
    'blacklist_ip' => 'VARCHAR(255) DEFAULT NULL',
  ),
  'link' => 
  array (
    'link_id' => 'INTEGER PRIMARY KEY AUTO_INCREMENT',
    'link_url' => 'VARCHAR(255) DEFAULT NULL',
    'link_title' => 'VARCHAR(255) DEFAULT NULL',
    'link_logo' => 'VARCHAR(255) DEFAULT NULL',
    'link_desc' => 'VARCHAR(255) DEFAULT NULL',
  ),
);