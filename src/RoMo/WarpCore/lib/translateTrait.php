<?php

namespace RoMo\Teleporter\lib;

trait translateTrait{

    /** @var array */
    protected static array $data;

    protected function initMessage(string $language) : void{
        $resourcePath = $this->getFile() . "resources/messages";
        $dataPath = $this->getDataFolder() . "messages/";

        @mkdir($dataPath);
        $dir = opendir($resourcePath);

        while(($read = readdir($dir))){
            if($read != "." && $read != ".."){
                //if(!file_exists($dataPath . "/" . $read)){
                    $messageFile = $resourcePath . "/" . $read;
                    copy($messageFile, $dataPath . "/" . $read);
                //}
            }
        }

        self::$data = parse_ini_file($dataPath . $language . ".ini", false);
    }

    public static function getPrefix() : string{
        if(!isset(self::$data["prefix"])){
            return "prefix ";
        }
        return (string) self::$data["prefix"];
    }

    public static function getTranslate(string $id, array $parameters = []): string{
        if(!isset(self::$data[$id])){
            return $id;
        }
        $str = self::$data[$id];

        $count = 1;
        foreach($parameters as $parameter){
            $str = str_replace("&{$count}", $parameter, $str);
            $count++;
        }

        return $str;
    }

    public static function getMessage($id, array $parameters = []): string{
        return self::getPrefix() . self::getTranslate($id, $parameters);
    }

    public static function getCmd($id): array{
        $commandId = "command.{$id}";

        if(!isset(self::$data[$commandId . ".name"])){
            $commandName = $id;
        }else{
            $commandName = self::$data[$commandId . ".name"];
        }

        if(!isset(self::$data[$commandId . ".description"])){
            $commandDescription = $id;
        }else{
            $commandDescription = self::$data[$commandId . ".description"];
        }

        if(!isset(self::$data[$commandId . ".usageMessage"])){
            $commandUsageMessage = $id;
        }else{
            $commandUsageMessage = self::$data[$commandId . ".usageMessage"];
        }

        $aliases = [];

        $count = 1;

        while(isset(self::$data[$commandId . ".aliases.{$count}"])){
            $aliases[] = self::$data[$commandId . ".aliases.{$count}"];
            $count++;
        }

        return [
            "name" => $commandName,
            "description" => $commandDescription,
            "usageMessage" => $commandUsageMessage,
            "aliases" => $aliases
        ];
    }
}