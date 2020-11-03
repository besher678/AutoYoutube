<?php

declare(strict_types=1);

namespace besher\YouTube;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use jojoe77777\FormAPI;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;

class Main extends PluginBase implements Listener
{

    public $channelids;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->videoidz = new Config($this->getDataFolder(). "videoids.yml", Config::YAML,array("vidids" => array()));
        $this->idsforchannel = new Config($this->getDataFolder(). "channelids.yml", Config::YAML,array("ids" => array()));
        $this->saveDefaultConfig();
        $this->getResource("config.yml");
    }

    public function getSubs($channelid){
        $api_key = "AIzaSyAF0kAVWo-KsgB0I4BxyWLXdfp7WxPQ6F0";
        $api_response = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics&id=' . $channelid . '&fields=items/statistics/subscriberCount&key=' . $api_key);
        $api_response_decoded = json_decode($api_response, true);
        $subcount = $api_response_decoded["items"][0]["statistics"]["subscriberCount"];
        return $subcount;
    }

    public function getvideo($videoid){
        $apikey = "AIzaSyAF0kAVWo-KsgB0I4BxyWLXdfp7WxPQ6F0";
        $json = file_get_contents('https://www.googleapis.com/youtube/v3/videos?id=' . $videoid . '&key=' . $apikey . '&part=snippet');
        $ytdata = json_decode($json);
        $title = $ytdata->items[0]->snippet->title;
        return $title;
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $lable, array $args): bool
    {
        if ($cmd->getName() === "youtube" or $cmd->getName() === "yt") {
            if($sender instanceof Player){
                $this->chooseyoutube($sender);
            } else {
                $sender->sendMessage("Console cant open a ui");
            }
        }
        return true;
    }

    public function apply($player)
    {

        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

        $form = $api->createCustomForm(function (Player $player, array $data = null) {
            if ($data === null) {

                return true;
            }
            if(!isset($data[0]) or $data[1]){
                global $title;
                global $subcount;
                global $channelid;
                global $videoid;
                $channelid = $data[0];
            $subcount = $this->getsubs($channelid);
            $videoid = $data[1];
            $title = $this->getvideo($videoid);
            $this->results($player);
            } else {
                $player->sendMessage(TF::RED . "You have to enter a youtube channel and a video");
            }
        });

        $form->setTitle("§l§4YOUTUBE");
        $form->addInput("Enter channel Id", "Ex: UCidtDQeAkmCsCPg2osD4GkA");
        $form->addInput("Enter video Id", "Ex: sjo3VCmrdw4");
        $form->sendToPlayer($player);
        return $form;
    }

    public function results($player)
    {

        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

        $form = $api->createSimpleForm(function (Player $player, int $data = null) {
            $result = $data;

            if ($result === null) {

                return true;
            }

            switch ($result) {
                case 0:
                    break;
            }
        });
        global $subcount;
        global $title;
        global $channelid;
        global $consub;
        global $contitle;
        global $videoid;
        global $contitle;
        $contitle = $this->getConfig()->get("title");
        $consub =  $this->getConfig()->get("subs");
        $form->setTitle("§2§lRESULTS");
        if($subcount >= $consub){
            if(!in_array($channelid,$this->idsforchannel->get("ids"))){
                if(strpos($title, $contitle) !== false){
                    if(!in_array($videoid, $this->videoidz->get("vidids"))){
                    $form->setContent(TF::YELLOW . "Congratulations, We detected that you are eligble for Youtube rank\n\n" . TF::GREEN . "Your Youtube rank will be set in a bit, please wait...\n\n" . TF::YELLOW . "We are happy to have you playing on our server, and wish you the best of luck\n");
                    $ids = $this->idsforchannel->get("ids");
                    $ids[] = $channelid;
                    $this->idsforchannel->set("ids", $ids);
                    $this->idsforchannel->save();
                    $lolvidids = $this->videoidz->get("vidids");
                    $lolvidids[] = $videoid;
                    $this->videoidz->set("vidids", $lolvidids);
                    $this->videoidz->save();
                    $name = $player->getName();
                    $command = "setgroup $name Youtube";
                    $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
                    $player->addTitle(TF::GREEN . "Congratulations", TF::YELLOW . "You have recived §r§fYou§cTube");

                    } else {
                        $form->setContent(TF::YELLOW . "Uh oh , looks like the Youtube rank for that" . TF::RED . " video " . TF::YELLOW .  "has already been redeemd\n\n" . TF::RED . "If you belive this is false then please contact staff immediately!\n\n");
                    }
            } else {
                $form->setContent(TF::YELLOW . "We have found your video but it looks like your don't have $contitle in the video title\n\n" . TF::RED . "Please put $contitle in your video title and try agian\n\n");
            }
        } else {
            $form->setContent(TF::YELLOW . "Uh oh , looks like the Youtube rank for that" . TF::RED .  " channel " . TF::YELLOW . "has already been redeemd\n\n" . TF::RED . "If you belive this is false then please contact staff immediately!\n\n");
        }
    } else {
        $form->setContent(TF::YELLOW . "Looks like you dont have enough subs to get Youtube rank\n\n" . TF::RED . "Subs requierd $consub\nYour subs: $subcount\n\n\n");
    }
        $form->addButton("Exit");
        $form->sendToPlayer($player);
        return $form;
}

    public function chooseyoutube($player)
    {

        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

        $form = $api->createSimpleForm(function (Player $player, int $data = null) {

            $result = $data;

            if ($result === null) {

                return true;
            }

            switch ($result) {
                case 0:
                    $this->apply($player);
                    break;

                case 1:
                    $this->requirements($player);
                    break;
            }
        });
        $form->setTitle("Pick what to do");
        $form->addButton("Apply for YouTube rank");
        $form->addButton("See the requirements for YouTube rank");
        $form->sendToPlayer($player);
        return $form;
    }

    public function requirements($player)
    {

        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

        $form = $api->createSimpleForm(function (Player $player, int $data = null) {

            $result = $data;
            global $consub;
            global $contitle;

            if ($result === null) {

                return true;
            }

            switch ($result) {
                case 0:
                    break;
            }
        });
        $amountneedsub = $this->getConfig()->get("subs"); 
        $titleneed = $this->getConfig()->get("title"); 
        $form->setTitle("Requirements");
        $form->setContent("To get the Youtube rank you have to meet the following requirements: " . TF::GREEN . "\n\n-Have atleast " .  $amountneedsub . " subs.\n\n-Make a video on the server with the '$titleneed' somewhere in the title.\n\n");
        $form->addButton("Exit");
        $form->sendToPlayer($player);
        return $form;
    }
}
