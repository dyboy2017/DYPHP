<?php
/**
 * 核心类文件
 */

namespace fastphp;

// 框架根目录
defined('CORE_PATH') or define('CORE_PATH', __DIR__);


/**
 * fastphp框架核心
 */
class Fastphp {
    //配置内容
    protected $config = [];

    /**
     * 构造函数初始化类
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * 运行程序
     */
    public function run() {
        spl_autoload_register([$this, 'loadClass']);
        $this->setReporting();
        $this->removeMagicQuotes();
        $this->unregisterGlobals();
        $this->setDbConfig();
        $this->route();
    }

    /**
     * 路由处理
     */
    public function route() {
        $controllerName = $this->config['defaultController'];
        $actionName = $this->config['defaultAction'];
        $param = [];

        $url = $_SERVER['REQUEST_URI'];
        // 清除?之后的内容
        $position = strpos($url, '?');
        $url = $position === false ? $url : substr($url, 0, $position);
        // 清除前后的 /
        $url = trim($url , '/');
        if($url) {
            // 使用 / 分割字符串，并保存在数组中
            $urlArray = explode('/', $url);
            // 删除空的数组元素
            $urlArray = array_filter($urlArray);
            // 获取控制器名
            $controllerName = ucfirst($urlArray[0]);
            // 获取操作名
            array_shift($urlArray);
            $actionName = $urlArray ? $urlArray[0] : $actionName;
            // 获取URL参数
            array_shift($urlArray);
            $param = $urlArray ? $urlArray : array();
        }

        // 判断控制器和操作是否存在
        $controller = 'app\\controllers\\' . $controllerName . 'Controller';
        if (!class_exists($controller)) {
            exit($controller . '控制器不存在');
        }
        elseif (!method_exists($controller, $actionName)) {
            exit($actionName . '方法不存在');
        }

        // 实例化
        $dispatch = new $controller($controllerName, $actionName);
        // 等同于 $dispatch->$actionName($param)
        call_user_func_array([$dispatch, $actionName], $param);
    }

    /**
     * 是否开启DEBUG
     */
    public function setReporting() {
        if (APP_DEBUG === TRUE) {
            error_reporting(7);
            ini_set('display_errors', 'On');
        } else {
            error_reporting(0);
            ini_set('display_errors','Off');
            ini_set('log_errors', 'On');
        }
    }

    /**
     * 检测敏感字符 并删除
     */
    public function removeMagicQuotes() {
        if (get_magic_quotes_gpc()) {
            $_GET = isset($_GET) ? $this->stripSlashesDeep($_GET ) : '';
            $_POST = isset($_POST) ? $this->stripSlashesDeep($_POST ) : '';
            $_COOKIE = isset($_COOKIE) ? $this->stripSlashesDeep($_COOKIE) : '';
            $_SESSION = isset($_SESSION) ? $this->stripSlashesDeep($_SESSION) : '';
        }
    }

    /**
     * 检测自定义全局变量并移除
     */
    public function unregisterGlobals() {
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    /**
     * 配置数据库信息
     */
    public function setDbConfig() {
        if ($this->config['db']) {
            define('DB_HOST', $this->config['db']['host']);
            define('DB_NAME', $this->config['db']['name']);
            define('DB_USER', $this->config['db']['user']);
            define('DB_PASS', $this->config['db']['pass']);
        }
    }

    /**
     * 自动加载类
     */
    public function loadClass($className) {
        $classMap = $this->classMap();

        if(isset($classMap[$className])) {
            // 包含该类文件
            $file = $classMap[$className];
        } elseif (strpos($className, '\\') !== false) {
            // 包含应用 app 下的文件
            $file = APP_PATH . str_replace('\\', '/', $className) . '.php';
            if (!is_file($file)) {
                return;
            }
        } else {
            return;
        }
        include $file;
    }

    // 内核文件命名空间映射关系
    public function classMap() {
        return [
            'fastphp\base\Controller' => CORE_PATH . '/base/Controller.php',
            'fastphp\base\Model' => CORE_PATH . '/base/Model.php',
            'fastphp\base\View' => CORE_PATH . '/base/View.php',
            'fastphp\db\Db' => CORE_PATH . '/db/Db.php',
            'fastphp\db\Sql' => CORE_PATH . '/db/Sql.php',
        ];
    }



}






