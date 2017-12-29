<?php
class Uploader
{
	const STATIC_PATH = HTTP_IMAGE;
	const STATIC_PATH_ROOT = DIR_STATIC;
	private $files = null;		// 文件源，php的$_FILES
	private $fileField = null; //文件域名
	private $file = null; //文件上传对象
	private $base64 = null; //文件上传对象
	private $config = null; //配置信息
	private $oriName = null; //原始文件名
	private $fileName = null; //新文件名
	private $fullName = null; //完整文件名,即从当前配置目录开始的URL
	private $filePath = null; //完整文件名,即从当前配置目录开始的URL
	private $middleImage = array();
	private $smallImage = array();
	private $fileSize = null; //文件大小
	private $fileType = null; //文件类型
	private $stateInfo = null; //上传状态信息,
	private $state = null; //上传状态,
	private $useOriNameCode = false;
	private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
		'SUCCESS', //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
		'文件大小超出 upload_max_filesize 限制',
		'文件大小超出 MAX_FILE_SIZE 限制',
		'文件未被完整上传',
		'没有文件被上传',
		'上传文件为空',
		'ERROR_TMP_FILE' => '临时文件错误',
		'ERROR_TMP_FILE_NOT_FOUND' => '找不到临时文件',
		'ERROR_SIZE_EXCEED' => '文件大小超出网站限制',
		'ERROR_TYPE_NOT_ALLOWED' => '文件类型不允许',
		'ERROR_CREATE_DIR' => '目录创建失败',
		'ERROR_DIR_NOT_WRITEABLE' => '目录没有写权限',
		'ERROR_FILE_MOVE' => '文件保存时出错',
		'ERROR_FILE_NOT_FOUND' => '找不到上传文件',
		'ERROR_WRITE_CONTENT' => '写入文件内容错误',
		'ERROR_UNKNOWN' => '未知错误',
		'ERROR_DEAD_LINK' => '链接不可用',
		'ERROR_HTTP_LINK' => '链接不是http链接',
		'ERROR_HTTP_CONTENTTYPE' => '链接contentType不正确',
		'INVALID_URL' => '非法 URL',
		'INVALID_IP' => '非法 IP'
	);

	/**
	 * 构造函数
	 * @param string $fileField 表单名称
	 * @param array $config 配置项
	 * @param array $files 文件源
	 * @param bool $base64 是否解析base64编码，可省略。若开启，则$fileField代表的是base64编码的字符串表单名
	 */
	public function __construct($fileField, $config, $type = "upload", $files = array())
	{
		$this->files = empty($files) ? $_FILES : $files;
		$this->fileField = $fileField;
		$this->config = $config;
		if(isset($this->config['useOriName']) && $this->config['useOriName']){
			$this->useOriNameCode = true;
		}
		$this->type = $type;
		if ($type == "remote") {
			$this->saveRemote();
		} else if ($type == "base64") {
			$this->upBase64();
		} else if($type == "more_upload"){
			$this->upFileMoreImage(true);
		} else {
			$this->upFile();
		}

	}

	/**
	 * 上传文件的主处理方法
	 * @return mixed
	 */
	private function upFile()
	{
		$file = $this->file = $this->files[$this->fileField];
		if (!$file) {
			$this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
			return false;
		}
		if ($this->file['error']) {									// 系统上传错误
			$this->stateInfo = $this->getStateInfo($file['error']);
			return false;
		} else if (!file_exists($file['tmp_name'])) {					// 文件是否存在
			$this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE_NOT_FOUND");
			return false;
		} else if (!is_uploaded_file($file['tmp_name'])) {			// 是否上传文件
			$this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE");
			return false;
		}

		// 源文件名称
		$this->oriName = $file['name'];
		// 源文件大小
		$this->fileSize = $file['size'];
		// 源文件格式
		$this->fileType = $this->getFileExt();
		$this->fullName = $this->getFullName();
		$this->filePath = $this->getFilePath();//self::STATIC_PATH_ROOT. 'catalog/' . date('YmdHis') . (int)rand(1000, 9999) . $this->fileType;
		$this->fileName = $this->getFileName();
		$dirname = dirname($this->filePath);

		//检查文件大小是否超出限制
		if (!$this->checkSize()) {
			$this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
			return false;
		}

		//检查是否不允许的文件格式
		if (!$this->checkType()) {
			$this->stateInfo = $this->getStateInfo("ERROR_TYPE_NOT_ALLOWED");
			return false;
		}

		//创建目录失败
		if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
			$this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
			return false;
		} else if (!is_writeable($dirname)) {
			$this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
			return false;
		}

		//移动文件
		if (!(move_uploaded_file($file["tmp_name"], $this->filePath) && file_exists($this->filePath))) { //移动失败
			$this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
			return false;
		} else { //移动成功
			$this->stateInfo = $this->stateMap[0];
			return true;
		}
	}


	/**
	 * 上传文件的处理方法（并生成缩略图）
	 * @return mixed
	 */
	private function upFileMoreImage($code = false)
	{
		$file = $this->file = $this->files[$this->fileField];
		if (!$file) {
			$this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
			return false;
		}
		if ($this->file['error']) {									// 系统上传错误
			$this->stateInfo = $this->getStateInfo($file['error']);
			return false;
		} else if (!file_exists($file['tmp_name'])) {					// 文件是否存在
			$this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE_NOT_FOUND");
			return false;
		} else if (!is_uploaded_file($file['tmp_name'])) {			// 是否上传文件
			$this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE");
			return false;
		}

		// 源文件名称
		$this->oriName = $file['name'];
		// 源文件大小
		$this->fileSize = $file['size'];
		// 源文件格式
		$this->fileType = $this->getFileExt();
		$this->fullName = $this->getFullName();
		$this->filePath = $this->getFilePath();//self::STATIC_PATH_ROOT. 'catalog/' . date('YmdHis') . (int)rand(1000, 9999) . $this->fileType;
		$this->fileName = $this->getFileName();
		$dirname = dirname($this->filePath);

		//检查文件大小是否超出限制
		if (!$this->checkSize()) {
			$this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
			return false;
		}

		//检查是否不允许的文件格式
		if (!$this->checkType()) {
			$this->stateInfo = $this->getStateInfo("ERROR_TYPE_NOT_ALLOWED");
			return false;
		}

		//创建目录失败
		if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
			$this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
			return false;
		} else if (!is_writeable($dirname)) {
			$this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
			return false;
		}

		//移动文件
		if (!(move_uploaded_file($file["tmp_name"], $this->filePath) && file_exists($this->filePath))) { //移动失败
			$this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
			return false;
		} else { //移动成功
			$this->stateInfo = $this->stateMap[0];
			// 生成中图和小图；
			if($code){
				$image_info = getimagesize($this->filePath);
				if(empty($image_info)){
					return false;
				}

				// 载入原图
				$imgType = substr($this->fileType,1);
				$createFun = 'imagecreatefrom'.($imgType=='jpg'?'jpeg':$imgType);
				if(!function_exists($createFun))
					$createFun = 'imagecreatefromjpeg';
				$srcImg = $createFun($this->filePath);
				$image_url_arr = explode('.',$this->fileName);
				$image_func = 'image'.($imgType=='jpg'?'jpeg':$imgType);
				$base_file_path = pathinfo($this->filePath);
				//相对路径；
				$relative_file_path = pathinfo($this->fullName);
				// 中图；
				$maxWidth 	 = $image_info[0] * 0.7;
				$maxHeight 	 = $image_info[1] * 0.7;
				$middleimage = $this->make_thumb($srcImg,$image_info[0],$image_info[1],$this->type,$maxWidth,$maxHeight,$gen = 1,$interlace = 0);
				$middle_new_path = $image_url_arr[0].'_middle.'.$image_url_arr[1];
				$image_func($middleimage,$base_file_path['dirname'].'/'.$middle_new_path);
				$this->middleImage = array('all_url' => HTTP_IMAGE.$relative_file_path['dirname'].'/'.$middle_new_path, 'url' => $relative_file_path['dirname'].'/'.$middle_new_path);
				// 小图；
				$maxWidth 	= $image_info[0] * 0.5;
				$maxHeight 	= $image_info[1] * 0.5;
				$smallImage = $this->make_thumb($srcImg,$image_info[0],$image_info[1],$this->type,$maxWidth,$maxHeight,$gen = 1,$interlace = 0);
				$small_new_path = $image_url_arr[0].'_small.'.$image_url_arr[1];
				$image_func($smallImage,$base_file_path['dirname'].'/'.$small_new_path);
				$this->smallImage = array('all_url' => HTTP_IMAGE.$relative_file_path['dirname'].'/'.$small_new_path, 'url' => $relative_file_path['dirname'].'/'.$small_new_path);
			}

			return true;
		}
	}

	/**
	 * 处理base64编码的图片上传
	 * @return mixed
	 */
	private function upBase64()
	{
		$base64Data = $_POST[$this->fileField];
		$info = $this->getBase64ImageInfo($base64Data);
		if(in_array($info['type'], array('jpeg', 'jpg', 'png', 'gif'))) {
            $this->fileType = '.' . $info['type'];
            $this->oriName = $this->fileField . '.' . $info['type'];
        }
        else {
            $this->stateInfo = $this->getStateInfo("ERROR_TYPE_NOT_ALLOWED");
            return;
        }

        $img = base64_decode($info['data']);
        if($img===FALSE) {
            $this->stateInfo = $this->getStateInfo(3);
            return;
        }

		$this->fileSize = strlen($img);
//		$this->fileType = $this->getFileExt();
		$this->fullName = $this->getFullName();
		$this->filePath = $this->getFilePath();
		$this->fileName = $this->getFileName();
		$dirname = dirname($this->filePath);

		//检查文件大小是否超出限制
		if (!$this->checkSize()) {
			$this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
			return;
		}

		//创建目录失败
		if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
			$this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
			return;
		} else if (!is_writeable($dirname)) {
			$this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
			return;
		}

		//移动文件
		if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
			$this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
		} else { //移动成功
			$this->stateInfo = $this->stateMap[0];
		}

	}

    private function getBase64ImageInfo($base64_image_content) {
        $info = array();
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $info['type'] = $result[2];
            $info['data'] = str_replace($result[1], '', $base64_image_content);
        }
        return $info;
    }
	/**
	 * 拉取远程图片
	 * @return mixed
	 */
	private function saveRemote()
	{
		$imgUrl = htmlspecialchars($this->fileField);
		$imgUrl = str_replace("&amp;", "&", $imgUrl);

		//http开头验证
		if (strpos($imgUrl, "http") !== 0) {
			$this->stateInfo = $this->getStateInfo("ERROR_HTTP_LINK");
			return;
		}

		preg_match('/(^https*:\/\/[^:\/]+)/', $imgUrl, $matches);
		$host_with_protocol = count($matches) > 1 ? $matches[1] : '';

		// 判断是否是合法 url
		if (!filter_var($host_with_protocol, FILTER_VALIDATE_URL)) {
			$this->stateInfo = $this->getStateInfo("INVALID_URL");
			return;
		}

		preg_match('/^https*:\/\/(.+)/', $host_with_protocol, $matches);
		$host_without_protocol = count($matches) > 1 ? $matches[1] : '';

		// 此时提取出来的可能是 ip 也有可能是域名，先获取 ip
		$ip = gethostbyname($host_without_protocol);
		// 判断是否是私有 ip
		if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
			$this->stateInfo = $this->getStateInfo("INVALID_IP");
			return;
		}

		//获取请求头并检测死链
		$heads = get_headers($imgUrl, 1);
		if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
			$this->stateInfo = $this->getStateInfo("ERROR_DEAD_LINK");
			return;
		}
		//格式验证(扩展名验证和Content-Type验证)
		$fileType = strtolower(strrchr($imgUrl, '.'));
		if (!in_array($fileType, $this->config['allowFiles']) || !isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image")) {
			$this->stateInfo = $this->getStateInfo("ERROR_HTTP_CONTENTTYPE");
			return;
		}

		//打开输出缓冲区并获取远程图片
		ob_start();
		$context = stream_context_create(
			array('http' => array(
				'follow_location' => false // don't follow redirects
			))
		);
		readfile($imgUrl, false, $context);
		$img = ob_get_contents();
		ob_end_clean();
		preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

		$this->oriName = $m ? $m[1] : "";
		$this->fileSize = strlen($img);
		$this->fileType = $this->getFileExt();
		$this->fullName = $this->getFullName();
		$this->filePath = $this->getFilePath();
		$this->fileName = $this->getFileName();
		$dirname = dirname($this->filePath);

		//检查文件大小是否超出限制
		if (!$this->checkSize()) {
			$this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
			return;
		}

		//创建目录失败
		if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
			$this->stateInfo = $this->getStateInfo("ERROR_CREATE_DIR");
			return;
		} else if (!is_writeable($dirname)) {
			$this->stateInfo = $this->getStateInfo("ERROR_DIR_NOT_WRITEABLE");
			return;
		}

		//移动文件
		if (!(file_put_contents($this->filePath, $img) && file_exists($this->filePath))) { //移动失败
			$this->stateInfo = $this->getStateInfo("ERROR_WRITE_CONTENT");
		} else { //移动成功
			$this->stateInfo = $this->stateMap[0];
		}

	}

	/**
	 * 上传错误检查
	 * @param $errCode
	 * @return string
	 */
	private function getStateInfo($errCode)
	{
		return !$this->stateMap[$errCode] ? $this->stateMap["ERROR_UNKNOWN"] : $this->stateMap[$errCode];
	}

	/**
	 * 获取文件扩展名
	 * @return string
	 */
	private function getFileExt()
	{
		return strtolower(strrchr($this->oriName, '.'));
	}

	/**
	 * 重命名文件
	 * @return string
	 */
	private function getFullName()
	{
		//替换日期事件
		$t = time();
		$d = explode('-', date("Y-y-m-d-H-i-s"));
		$format = $this->config["pathFormat"];
		$format = str_replace("{yyyy}", $d[0], $format);
		$format = str_replace("{yy}", $d[1], $format);
		$format = str_replace("{mm}", $d[2], $format);
		$format = str_replace("{dd}", $d[3], $format);
		$format = str_replace("{hh}", $d[4], $format);
		$format = str_replace("{ii}", $d[5], $format);
		$format = str_replace("{ss}", $d[6], $format);
		$format = str_replace("{time}", $t, $format);

		//过滤文件名的非法自负,并替换文件名
		$oriName = substr($this->oriName, 0, strrpos($this->oriName, '.'));
		$oriName = preg_replace("/[\|\?\"\<\>\/\*\\\\]+/", '', $oriName);
		$format = str_replace("{filename}", $oriName, $format);

		//替换随机字符串
		$randNum = rand(1000000000, 9999999999) . rand(1000000000, 9999999999);
		if (preg_match("/\{rand\:([\d]*)\}/i", $format, $matches)) {
			$format = preg_replace("/\{rand\:[\d]*\}/i", substr($randNum, 0, $matches[1]), $format);
		}

		//如果规定了不用原来的文件名
		if($this->useOriNameCode && $this->oriName){
			$ori_name_arr =explode('.',$this->oriName);
			if(isset($ori_name_arr[0]) && $ori_name_arr[0]){
				$format = '/images/ad/'. $ori_name_arr[0];
			}
		}

		$ext = $this->getFileExt();
		return $format . $ext;
	}

	/**
	 * 获取文件名
	 * @return string
	 */
	private function getFileName()
	{
		return substr($this->filePath, strrpos($this->filePath, '/') + 1);
	}

	/**
	 * 获取文件完整路径
	 * @return string
	 */
	private function getFilePath()
	{
		$fullname = $this->fullName;
		$rootPath = self::STATIC_PATH_ROOT;

		if (substr($fullname, 0, 1) != '/') {
			$fullname = '/' . $fullname;
		}

		return $rootPath . $fullname;
	}

	/**
	 * 文件类型检测
	 * @return bool
	 */
	private function checkType()
	{
		return in_array($this->getFileExt(), $this->config["allowFiles"]);
	}

	/**
	 * 文件大小检测
	 * @return bool
	 */
	private function  checkSize()
	{
		return $this->fileSize <= ($this->config["maxSize"]);
	}

	/**
	 * 获取当前上传成功文件的各项信息
	 * @return array
	 */
	public function getFileInfo()
	{
		return array(
			"state" => $this->stateInfo,
			"url" => self::STATIC_PATH . $this->fullName,
			"title" => $this->fileName,
			"original" => $this->oriName,
			"type" => $this->fileType,
			"size" => $this->fileSize,
            "filePath" => $this->fullName,
			"middleImage" => $this->middleImage,
			"smallImage" => $this->smallImage
		);
	}

	public function make_thumb($srcImg,$srcWidth,$srcHeight,$type,$maxWidth=200,$maxHeight=50,$gen = 0,$interlace = 0)
	{
		$interlace = $interlace ? 1:0;
		if($maxWidth > 0 && $maxHeight > 0)
			$scale = min($maxWidth/$srcWidth, $maxHeight/$srcHeight); // 计算缩放比例
		elseif($maxWidth == 0)
			$scale = $maxHeight/$srcHeight;
		elseif($maxHeight == 0)
			$scale = $maxWidth/$srcWidth;
		if($scale >= 1)
		{
			// 超过原图大小不再缩略
			$width  = $srcWidth;
			$height = $srcHeight;
		}
		else
		{
			// 缩略图尺寸
			$width = (int)($srcWidth*$scale);
			$height = (int)($srcHeight*$scale);
		}
		if($gen == 1)
		{
			$width = $maxWidth;
			$height = $maxHeight;
		}
		//创建缩略图
		if($type!='gif' && function_exists('imagecreatetruecolor'))
			$thumbImg = imagecreatetruecolor($width, $height);
		else
			$thumbImg = imagecreatetruecolor($width, $height);
		//分配颜色
		$color = imagecolorAllocate($thumbImg,255,255,255);   //分配一个灰色
		imagefill($thumbImg,0,0,$color);

		$x = 0;
		$y = 0;
		if($gen == 1 && $maxWidth > 0 && $maxHeight > 0)
		{
			$resize_ratio = $maxWidth/$maxHeight;
			$src_ratio = $srcWidth/$srcHeight;
			if($src_ratio >= $resize_ratio)
			{
				$x = ($srcWidth - ($resize_ratio * $srcHeight)) / 2;
				$width = ($height * $srcWidth) / $srcHeight;
			}
			else
			{
				$y = ($srcHeight - ( (1 / $resize_ratio) * $srcWidth)) / 2;
				$height = ($width * $srcHeight) / $srcWidth;
			}
		}
		// 复制图片
		if(function_exists("imagecopyresampled"))
			imagecopyresampled($thumbImg, $srcImg, 0, 0, $x, $y, $width, $height, $srcWidth,$srcHeight);
		else
			imagecopyresized($thumbImg, $srcImg, 0, 0, $x, $y, $width, $height, $srcWidth,$srcHeight);
		if('gif'==$type || 'png'==$type) {
			$background_color = imagecolorallocate($thumbImg, 255,255,255); // 指派一个颜色
			imagecolortransparent($thumbImg,$background_color); // 设置为透明色，若注释掉该行则输出绿色的图
		}
		// 对jpeg图形设置隔行扫描
		if('jpg'==$type || 'jpeg'==$type)
			imageinterlace($thumbImg,$interlace);
		return $thumbImg;
	}

}