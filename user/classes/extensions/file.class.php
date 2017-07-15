<?php
namespace extensions;

class file{
	static function uploadZip($input){
		if(error()) return error();
		if(!is_admin()) return error(lang('mod.permissionDenied'));
		if(empty($input['type'])) return error(lang('mod.missingArguments'));
		$result = \file::upload($input);
		if($result['success']){
			$data = $result['data'][0];
			$src = __ROOT__.substr($data['file_src'], strlen(site_url()));
			if($input['type'] == 'template'){
				$folder = 'app/template/';
			}else{
				$folder = config('mod.plugins.savePath');
			}
			if(zip_extract($src, __ROOT__.$folder)){
				return success(lang('file.uploadedTip'));
			}else{
				error(lang('file.extractionError'));
			}
		}else{
			return error($result['data']);
		}
	}

	static function removeFolder($input){
		if(error()) return error();
		if(config('mod.installed') && !is_admin())
			return error(lang('mod.permissionDenied'));
		if(empty($input['folder']) || empty($input['type']))
			return error(lang('mod.missingArguments'));
		if($input['type'] == 'template'){
			$dir = __ROOT__.'app/template/'.$input['folder'];
			if(strpos(template_path(), $dir) === 0)
				return error(lang('admin.deleteInUseTemplateWarning'));
			if(is_dir($dir) && xrmdir($dir)){
				$tmp = __ROOT__.config('mod.template.compiler.savePath').'app/template/'.$input['folder'];
				if(is_dir($tmp)) xrmdir($tmp);
				return success(lang('admin.templateDeleteSuccess'));
			}else{
				return error(lang('admin.templateDeleteFail'));
			}
		}else{
			$enable = explode('|', str_replace(' ', '', config('mod.plugins.enable')));
			if(in_array($input['folder'], $enable))
				return error(lang('admin.deleteInUsePluginWarning'));
			$dir = __ROOT__.config('mod.plugins.savePath').$input['folder'];
			if(is_dir($dir) && xrmdir($dir)){
				$tmp = __ROOT__.config('mod.template.compiler.savePath').'app/plugins/'.$input['folder'];
				if(is_dir($tmp)) xrmdir($tmp);
				return success(lang('admin.pluginDeleteSuccess'));
			}else{
				return error(lang('admin.pluginDeleteFail'));
			}
		}
	}

	static function getContent($input){
		if(error()) return error();
		if(!is_admin()) return error(lang('mod.permissionDenied'));
		if(empty($input['filename']))
			return error(lang('mod.missingArguments'));
		$file = __ROOT__.'app/'.$input['filename'];
		if(!file_exists($file))
			return error(lang('mod.notExists', lang('file.label')));
		return success(file_get_contents($file));
	}

	static function putContent($input){
		if(error()) return error();
		if(!is_admin()) return error(lang('mod.permissionDenied'));
		if(empty($input['filename']) || empty($input['content']))
			return error(lang('mod.missingArguments'));
		$file = __ROOT__.'app/'.$input['filename'];
		if(!file_exists($file))
			return error(lang('mod.notExists', lang('file.label')));
		if(file_put_contents($file, $input['content'])){
			return success(lang('file.editSuccess'));
		}else{
			return error(lang('file.editFail'));
		}
	}

	static function removeTempFolder($input){
		if(error()) return error();
		if(config('mod.installed') && !is_admin())
			return error(lang('mod.permissionDenied'));
		if(empty($input['folder'])) return error(lang('mod.missingArguments'));
		$dir = \template::$saveDir.'app/'.$input['folder'];
		if(file_exists($dir)){
			xrmdir($dir);
			return success(lang('admin.tempDeleteSuccess'));
		}
		return error(lang('admin.tempDeleteFail'));
	}
}