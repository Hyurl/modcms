<?php
/** 添加/更新/删除链接时检查用户权限 */
add_action(array('link.add', 'link.update', 'link.delete'), 'link::checkPermission');