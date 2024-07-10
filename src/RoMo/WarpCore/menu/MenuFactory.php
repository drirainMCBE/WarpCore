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

    /** @var Menu[] */
    private array $menus = [];

    public static function init() : void{
        self::$instance = new self();
    }

    private function __construct(){
        $dataPath = Path::join(WarpCore::getInstance()->getDataFolder() . "menu.json");
        if(is_file($dataPath)){
            $data = json_decode(mb_convert_encoding(file_get_contents($dataPath), "UTF-8", "auto"), true);

            /** @var \Closure[] $makingMenuClosures */
            $makingMenuClosures = [];

            foreach($data as $id => $menuData){
                [$this->menus[$id], $makingMenuClosures[]] = Menu::create($id, $menuData);
            }
            foreach($makingMenuClosures as $makingMenuClosure){
                $makingMenuClosure($this->menus);
            }

            if(!isset($this->menus[self::MAIN_MENU_NAME])){
                [$this->menus[self::MAIN_MENU_NAME], ] = Menu::create(self::MAIN_MENU_NAME, []);
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