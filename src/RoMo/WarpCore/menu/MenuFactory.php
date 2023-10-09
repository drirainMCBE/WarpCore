<?php

declare(strict_types=1);

namespace RoMo\WarpCore\menu;

use JsonException;
use pocketmine\utils\SingletonTrait;
use RoMo\WarpCore\warp\WarpFactory;
use RoMo\WarpCore\WarpCore;

class MenuFactory{

    use SingletonTrait;

    /** @var string */
    private string $dataPath;

    public static function init(){
        self::$instance = new self();
    }

    /**
     * @throws JsonException
     */
    private function __construct(){
        $this->dataPath = WarpCore::getInstance()->getDataFolder() . "menu.json";
        if(!is_file($this->dataPath)){
            $this->save();
        }
        $data = json_decode(file_get_contents($this->dataPath), true, 512, JSON_THROW_ON_ERROR);
        foreach($data as $name => $warpsData){
            $warps = [];
            foreach($warpsData as $warpData){
                $warp = WarpFactory::getInstance()->getWarp($warpData);
                if($warp !== null){
                    $warps[$warp->getName()] = $warp;
                }
            }
            $this->categories[$name] = new Category($name, $warps);
        }
    }

    /**
     * @throws JsonException
     */
    public function save() : void{
        $data = [];
        foreach($this->categories as $category){
            $warps = [];
            foreach($category->getAllWarp() as $warp){
                $warps[] = $warp->getName();
            }
            $data[$category->getName()] = $warps;
        }
        file_put_contents($this->dataPath, json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * @param string $name
     *
     * @return Category|null
     */
    public function getCategory(string $name) : ?Category{
        return $this->categories[$name] ?? null;
    }

    /**
     * @return Category[]
     */
    public function getAllCategory() : array{
        return $this->categories;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isExistCategory(string $name) : bool{
        return !is_null($this->getCategory($name));
    }

    /**
     * @param Category $category
     */
    public function addCategory(Category $category) : void{
        $this->categories[$category->getName()] = $category;
    }

    /**
     * @param Category $category
     */
    public function removeCategory(Category $category) : void{
        if(!isset($this->categories[$category->getName()])){
            return;
        }
        unset($this->categories[$category->getName()]);
    }
}