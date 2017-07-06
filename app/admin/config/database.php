<?php
return array (
  "user" => 
  array (
    "user_id" => "INTEGER PRIMARY KEY AUTO_INCREMENT",
    "user_name" => "VARCHAR(255) DEFAULT ''",
    "user_nickname" => "VARCHAR(255) DEFAULT ''",
    "user_realname" => "VARCHAR(255) DEFAULT ''",
    "user_password" => "VARCHAR(255) DEFAULT ''",
    "user_gender" => "VARCHAR(255) DEFAULT ''",
    "user_avatar" => "VARCHAR(255) DEFAULT ''",
    "user_identity" => "VARCHAR(255) DEFAULT ''",
    "user_company" => "VARCHAR(255) DEFAULT ''",
    "user_number" => "VARCHAR(255) DEFAULT ''",
    "user_email" => "VARCHAR(255) DEFAULT ''",
    "user_tel" => "VARCHAR(255) DEFAULT ''",
    "user_desc" => "VARCHAR(255) DEFAULT ''",
    "user_protect" => "VARCHAR(255) DEFAULT ''",
    "user_level" => "INTEGER DEFAULT 1",
  ),
  "category" => 
  array (
    "category_id" => "INTEGER PRIMARY KEY AUTO_INCREMENT",
    "category_name" => "VARCHAR(255) DEFAULT ''",
    "category_alias" => "VARCHAR(255) DEFAULT ''",
    "category_desc" => "VARCHAR(255) DEFAULT ''",
    "category_parent" => "INTEGER DEFAULT 0",
    "category_posts" => "INTEGER DEFAULT 0",
    "category_children" => "INTEGER DEFAULT 0",
  ),
  "file" => 
  array (
    "file_id" => "INTEGER PRIMARY KEY AUTO_INCREMENT",
    "file_name" => "VARCHAR(255) DEFAULT ''",
    "file_src" => "VARCHAR(255) DEFAULT ''",
    "file_desc" => "VARCHAR(255) DEFAULT ''",
    "file_time" => "INTEGER DEFAULT 0",
    "user_id" => "INTEGER DEFAULT 0",
  ),
  "post" => 
  array (
    "post_id" => "INTEGER PRIMARY KEY AUTO_INCREMENT",
    "post_title" => "VARCHAR(255) DEFAULT ''",
    "post_content" => "TEXT",
    "post_thumbnail" => "VARCHAR(255) DEFAULT ''",
    "post_commentable" => "INTEGER DEFAULT 0",
    "post_comments" => "INTEGER DEFAULT 0",
    "post_time" => "INTEGER DEFAULT 0",
    "post_link" => "VARCHAR(255) DEFAULT ''",
    "user_id" => "INTEGER DEFAULT 0",
    "category_id" => "INTEGER DEFAULT 0",
    "post_type" => "INTEGER DEFAULT 0", //文章类型，0(默认)，1(短文), 2(页面)，3(草稿)
    "post_original" => "INTEGER DEFAULT 0",  //是否原创
    "post_tags" => "VARCHAR(255) DEFAULT ''", //文章标签
    "post_desc" => "VARCHAR(1023) DEFAULT ''", //文章摘要
  ),
  "comment" => 
  array (
    "comment_id" => "INTEGER PRIMARY KEY AUTO_INCREMENT",
    "comment_content" => "VARCHAR(1023) DEFAULT ''",
    "comment_time" => "INTEGER DEFAULT 0",
    "comment_parent" => "INTEGER DEFAULT 0",
    "comment_parent_uid" => "INTEGER DEFAULT 0",
    "comment_replies" => "INTEGER DEFAULT 0",
    "comment_status" => "INTEGER DEFAULT 0", //评论状态，0 待审核, 1 已发布
    "comment_ip" => "VARCHAR(255) DEFAULT ''",
    "post_id" => "INTEGER DEFAULT 0",
    "user_id" => "INTEGER DEFAULT 0",
  ),
  "blacklist" => 
  array (
    "blacklist_id" => "INTEGER PRIMARY KEY AUTO_INCREMENT",
    "blacklist_uid" => "INTEGER DEFAULT 0",
    "blacklist_email" => "VARCHAR(255) DEFAULT ''",
    "blacklist_ip" => "VARCHAR(255) DEFAULT ''",
  ),
  "link" => 
  array (
    "link_id" => "INTEGER PRIMARY KEY AUTO_INCREMENT",
    "link_url" => "VARCHAR(255) DEFAULT ''",
    "link_title" => "VARCHAR(255) DEFAULT ''",
    "link_logo" => "VARCHAR(255) DEFAULT ''",
    "link_desc" => "VARCHAR(255) DEFAULT ''",
  ),
);