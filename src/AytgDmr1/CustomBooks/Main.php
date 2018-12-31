<?php

declare(strict_types=1);

namespace AytgDmr1\CustomBooks;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\Item\Item;
use pocketmine\Item\ItemFactory;
use pocketmine\Item\ItemIds;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class Main extends PluginBase implements Listener{



	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this,$this);

		@mkdir($this->getDataFolder());

		$this->saveResource("lang.yml");
		$this->lang = new Config($this->getDataFolder() . "lang.yml", Config::YAML);

		$this->saveResource("books.yml");
		$this->books = new Config($this->getDataFolder() . "books.yml", Config::YAML);

		$this->saveResource("config.yml");
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

		$this->lang->reload();
		$this->books->reload();
		$this->config->reload();

		$this->getLogger()->info("CustomBooks plugin successfully loaded.");
	}

	public function giveBook($arg, $sender){
		$this->books->reload();
		$this->lang->reload();

		if (!$arg){
		$sender->sendMessage(TextFormat::colorize($this->lang->get("specify-book-name")));
			return true;
		}

		if (!array_key_exists($arg, $this->books->get('books'))){
			$sender->sendMessage(TextFormat::colorize($this->lang->get("book-not-found")));
			return true;
		}

		$getBook = $this->books->get('books')[$arg];


		
		$book = ItemFactory::get(ItemIds::WRITTEN_BOOK);


		for ($i=0; $i < count($getBook['pages']); $i++) { 
			$text = TextFormat::colorize($getBook['pages'][$i]);
			
			$book->setPageText($i, $text);
		}

		if (array_key_exists('permission', $getBook)){
			if (!$sender->hasPermission($getBook['permission'])){
				$sender->sendMessage(TextFormat::colorize($this->lang->get('no-permission')));
				return true;
			}
		}

		$book->setCustomName(TextFormat::colorize($getBook['name']));
		$sender->getInventory()->addItem($book);
		
	}

	public function onJoin(PlayerJoinEvent $event){
		$spawnBooks = $this->config->get('spawn-books');
		$player = $event->getPlayer();
		
		if (!$spawnBooks)
			return true;


		foreach ($this->config->get('spawn-books') as $key) {
			if (array_key_exists($key, $this->books->get('books'))){
				$this->giveBook($key, $player);
			}
		}
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		$this->books->reload();
		$this->lang->reload();

		switch($command->getName()){
			case "book":
				$this->giveBook($args[0], $sender);
				return true;

			case "books":
				$booklist = "";
				foreach (array_keys($this->books->get('books')) as $book) {
					$booklist .= $book . ", ";
				}

				$sender->sendMessage(TextFormat::colorize($this->lang->get('books') . $booklist));

				return true;
		}
	}

	public function onDisable() : void{
		$this->getLogger()->info("CustomBooks plugin disabled.");
	}
}
