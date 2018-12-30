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

class Main extends PluginBase{

	public function onEnable() : void{
		@mkdir($this->getDataFolder());

		$this->saveResource("lang.yml");
		$this->lang = new Config($this->getDataFolder() . "lang.yml", Config::YAML);

		$this->saveResource("books.yml");
		$this->books = new Config($this->getDataFolder() . "books.yml", Config::YAML);

		$this->lang->reload();
		$this->books->reload();

		$this->getLogger()->info("CustomBooks plugin successfully loaded.");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		$this->books->reload();
		$this->lang->reload();

		switch($command->getName()){
			case "book":
				if (!$args){
					$sender->sendMessage(TextFormat::colorize($this->lang->get("specify-book-name")));
					return true;
				}

				if (!array_key_exists($args[0], $this->books->get('books'))){
					$sender->sendMessage(TextFormat::colorize($this->lang->get("book-not-found")));
					return true;
				}

				$getBook = $this->books->get('books')[$args[0]];
				
				$book = ItemFactory::get(ItemIds::WRITTEN_BOOK);


				for ($i=0; $i < count($getBook['pages']); $i++) { 
					$text = TextFormat::colorize($getBook['pages'][$i]);
					
					$book->setPageText($i, $text);
				}

				$book->setCustomName(TextFormat::colorize($getBook['name']));
				$sender->getInventory()->addItem($book);
				return true;

			case "books":
				$booklist = "";
				foreach (array_keys($this->books->get('books')) as $book) {
					$booklist .= $book;
				}

				$sender->sendMessage(TextFormat::colorize($this->lang->get('books') . $booklist));

				return true;
		}
	}

	public function onDisable() : void{
		$this->getLogger()->info("CustomBooks plugin disabled.");
	}
}
