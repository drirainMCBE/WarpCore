<?php

declare(strict_types=1);

namespace RoMo\WarpCore\menu;

use pocketmine\utils\SingletonTrait;
use RoMo\WarpCore\warp\WarpFactory;
use RoMo\WarpCore\WarpCore;
use Symfony\Component\Filesystem\Path;

class MenuFactory{

    use SingletonTrait;

    const MAIN_MENU_NAME = "main";

    /** @var string */
    private string $dataPath;

    /** @var Menu[] */
    private array $menus = [];

    public static function init() : void{
        self::$instance = new self();
    }

    private function __construct(){
        $dataPath = Path::join(WarpCore::getInstance()->getDataFolder() . "menu.json");
        if(is_file($dataPath)){
            $data = json_decode(file_get_contents($dataPath), true);

            //다른 메뉴로 넘어가는 데이터를 빼고 메뉴를 모두 로딩
            foreach($data as $name => $menuData){
                $content = [];
                foreach($menuData as $contentData){
                    $warp = WarpFactory::getInstance()->getWarp($contentData);
                    if(is_null($warp)){
                        $content[] = $contentData;
                    }else{
                        $content[] = $warp;
                    }
                }
                $this->menus[$name] = new Menu($name, $content);
            }

            //메뉴의 내용에서 다른 메뉴로 넘어가는 내용을 메뉴 객체로 변경
            foreach($this->menus as $menu){
                $content = $menu->getContent();
                foreach($content as $key => $value){
                    if(is_string($value)){
                        if(isset($this->menus[$value])){
                           $content[$key] = $this->menus[$value];
                        }else{
                            unset($content[$key]);
                        }
                    }
                }
                $menu->setContent($content);
                $menu->initWarpMenuForm();
            }

            if(!isset($this->menus[self::MAIN_MENU_NAME])){
                $this->menus[self::MAIN_MENU_NAME] = new Menu(self::MAIN_MENU_NAME, []);
            }
        }else{
            file_put_contents($dataPath, "{}");
        }
    }

    /**
     * @return array
     */
    public function getAllMenus() : array{
        return $this->menus;
    }

    public function getMenu(string $name) : ?Menu{
        return $this->menus[$name] ?? null;
    }

    public function getMainMenu() : Menu{
        return $this->menus[self::MAIN_MENU_NAME];
    }
}