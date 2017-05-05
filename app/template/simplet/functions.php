<?php
config('mod.template.compiler.enable', 1);//启用编译器

$adminDir = rtrim(ADMIN_DIR, '/');

/** 加载语言包 */
/** 推荐在模板文件夹下使用一个独立的文件夹存放语言包，如 lang/ */
$file = template_dir('lang/'.strtolower(__LANG__).'.php');
if(file_exists($file)){
    lang(include($file));
}

/** list_category() 列出所有分类目录 */
function list_category($tree = array()){
	if(!$tree){
		$tree = category_tree();
	}
	if(!$tree) return;
	foreach ($tree as $cat) {
		$uri = config('category.staticURI');
		if(strpos($uri, '{page}')) $cat['page'] = 1;
		echo '<li data-id="'.$cat['category_id'].'"><a href="'.create_url($uri, $cat).'" title="'.$cat['category_desc'].'">'.($cat['category_alias'] ?: $cat['category_name']).'</a>';
		if($cat['category_children']){
			echo '<ul>';
			list_category($cat['category_children']);
			echo '</ul>';
		}
		echo '</li>';
	}
}

/** 预设配置 */
if(is_null(config('site.template.simplet.fixedWidth'))) config('site.template.simplet.fixedWidth', true); //固定宽度
if(is_null(config('site.template.simplet.fixedNavbar'))) config('site.template.simplet.fixedNavbar', true); //固定导航栏

function site_meta($key){
	$mata = array(
		'keywords'    => config('site.keywords'),
		'description' => config('site.description'),
		'author'      => config('site.author'),
		'generator'   => config('site.generator'),
		'base'        => site_url(),
		'title'       => config('site.name').(config('site.subname') ? ' - '.config('site.subname') : '')
		);
	if(is_category()){
		$mata['keywords'] .= ', '.category_name().', '.category_alias();
		$mata['description'] = category_desc();
		$mata['title'] = (category_alias() ?: category_name()).(!empty($_GET['page']) ? ' 第'.$_GET['page'].'页' : '').' - '.config('site.name');
	}elseif(is_single()){
		$mata['description'] = get_post_desc();
		$mata['title'] = post_title().' - '.config('site.name');
		$mata['keywords'] .= ', '.(str_replace('，', ', ', post_tags()) ?: post_category('name'));
		$meta['author'] = post_user('nickname') ?: post_user('name');
	}elseif(is_404()){
		$mata['title'] = '404 Not found - '.config('site.name');
	}
	if($key == 'base') $html = '<base href="';
	else if($key == 'title') $html = '<title>';
	else $html = '<meta name="'.$key.'" content="';
	$html .= $mata[$key];
	if($key == 'title') $html .= "</title>\n";
	else $html .= "\"/>\n";
	return $html;
}