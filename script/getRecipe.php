<?php

	// if the parameter url is sended
	if(isset($_POST["recipeLink"]) && !empty($_POST["recipeLink"]))
	{
		$recipeLink = $_POST["recipeLink"];		
		$filename = getFileNameByUrl($recipeLink);
		$fileHandle = fopen($filename, 'w') or die("can't open file");

		ini_set('user_agent', 'Mozilla/5.0');
		$doc = new DOMDocument();

		/* add @ because a lot of marmiton's html page for recipe are not valid 
		 * loadHTMLFile show error if a tag is not closed for example
		*/
		@$doc->loadHTMLFile($recipeLink);
		$finder = new DomXPath($doc);
		
		/* All class we need for aspirate good information */
		$recipeTitleClass = "m_title";
		$recipeInfoClass = "m_content_recette_info";
		$recipeImageClass = "photo";
		$recipeIngredientsClass = "m_content_recette_ingredients";
		$recipeTodoClass = "m_content_recette_todo";
		

		
		


		$recipeTitleNodeValue =  getNodeValueByClassName($recipeTitleClass,$finder);
		$recipeInfoNodeValue =  getNodeValueByClassName($recipeInfoClass,$finder);
		preg_match_all("/[^:]*minutes/",$recipeInfoNodeValue,$recipeInfoMatchs);
		$recipePhotoUrl = getPhotoUrlRecipeByClassName($recipeImageClass,$finder);
		$recipteIngredients =  getNodeValueByClassName($recipeIngredientsClass,$finder);
		$arrayIngredients = preg_split('/- |:/', $recipteIngredients);
		$recipeTodo =  getContentByClassName($recipeTodoClass,$finder);
		
		
		fwrite($fileHandle, "<?xml version=\"1.0\" encoding=\"utf-8\"?>");
		fwrite($fileHandle, "\r\n<Recette titre=\"".$recipeTitleNodeValue."\">");
		fwrite($fileHandle, "\r\n	<Infos>");
		fwrite($fileHandle, "\r\n		<TempsPrepa>".trim($recipeInfoMatchs[0][0])."</TempsPrepa>");
		fwrite($fileHandle, "\r\n		<TempsCuisson>".trim($recipeInfoMatchs[0][1])."</TempsCuisson>");
		fwrite($fileHandle, "\r\n		<PhotoUrl>".$recipePhotoUrl."</PhotoUrl>");
		fwrite($fileHandle, "\r\n	</Infos>");
		fwrite($fileHandle, "\r\n	<Ingredients>");

		foreach ($arrayIngredients as $ingredient)
		{
			$ingredientCleaned = trim($ingredient);
			
			if(strlen($ingredientCleaned) != 0)
				fwrite($fileHandle, "\r\n		<Ingredient>".$ingredientCleaned."</Ingredient>");
		}

		fwrite($fileHandle, "\r\n	</Ingredients>");
		fwrite($fileHandle, "\r\n	<Preparation>");
		fwrite($fileHandle, "\r\n".$recipeTodo);
		fwrite($fileHandle, "\r\n	</Preparation>");
		fwrite($fileHandle, "\r\n</Recette>");
		fclose($fileHandle);


		displayXml($filename);
	}

	function getContentByClassName($className,$finder)
	{
		$text = "";
		$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), '".$className."')]");
		$childNodes = $nodes->item(0)->childNodes;
		foreach( $childNodes as $item)
		{
			if(isset($item->tagName))
			{
				switch ($item->tagName) 
				{
					case 'a':
						$text .= "\r\n<lien url=\"".$item->getAttribute('href')."\">".$item->nodeValue."</lien>";
						
						break;
					case 'span':
						$text .= "<span>".$item->nodeValue."</span>";
						break;
					default:
						# do nothing for other tag
						break;
				}
			}
			else
			  $text .= $item->nodeValue;

		}
		$textWithoutDoubleSpace = preg_replace('/\s+/', ' ',$text);
		$textCleaned = trim($textWithoutDoubleSpace);
		
		return utf8_decode($textCleaned);

	}

	/*
	 ** Function which return the nodeValue cleaned with utf8
	 **	deleting doubles/space and useless space
	*/
	function getNodeValueByClassName($className,$finder)
	{
		$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), '".$className."')]");
		$content = $childNodes = $nodes->item(0)->nodeValue;
		$contentWithoutDoubleSpace = preg_replace('/\s+/', ' ',$content);
		$contentCleaned = trim($contentWithoutDoubleSpace);
		return utf8_decode($contentCleaned);
	}

	/*
	**
	*/
	function getPhotoUrlRecipeByClassName($className,$finder)
	{
		$nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), '".$className."')]");
		foreach ($nodes as $node) 
		{
			if(isset($node->tagName) && $node->tagName == "img")
			{
				
				$urlPhoto = $node->getAttribute('src');	
			}	
		}
		if(empty($urlPhoto))
			$urlPhoto = "http://placehold.it/350x350";
		return utf8_decode($urlPhoto);

	}

	/*
	** Function which return the name of the final filename to save
	*/

	function getFileNameByUrl($url)
	{
		preg_match("/recette_[^\.]*/",$url,$match);
		$fileName = "./recipe/".$match[0].".xml";
		return $fileName;
	}


	/*
	** Function whish traduce and display the xml file with SAX
	*/
	function displayXml($fileXmlName)
	{
			$sax = xml_parser_create();
			xml_parser_set_option($sax,XML_OPTION_CASE_FOLDING, false);
			xml_parser_set_option($sax,XML_OPTION_SKIP_WHITE,true);
			xml_parser_set_option($sax,XML_OPTION_SKIP_TAGSTART,false);
			xml_set_element_handler($sax, 'startTag', 'endTag');
			xml_set_character_data_handler($sax, 'sax_cdata');
			xml_set_character_data_handler ( $sax, 'tagContent' );


			xml_parse($sax, file_get_contents($fileXmlName),true);
	}

	/*
	** Calback startTag find in XML
	*/
	function startTag($sax,$tag,$attr)
	{
		if($tag == "Recette")
		{
			echo "<h1>".htmlspecialchars($attr['titre'])."</h1>";
		}
		if($tag == "Infos")
		{
			echo "<div id='container'><div id='containerInfo'>";
		}
		if($tag == "TempsPrepa")
		{
			echo "<h3>Temps de préparation: ";	
		}
		if($tag == "TempsCuisson")
		{
			echo "<h3>Temps de cuisson: ";	
		}
		if($tag == "PhotoUrl")
		{
			echo "<img src='";	
		}

		if($tag == "Ingredients")
		{
			echo "<ul id='containerIngredients'>";
		}
		if($tag == "Ingredient")
		{
			echo "<li>";
		}
		if($tag == "Preparation")
		{
			echo "</div><div id='containerPreparation'><h3>Préparation:</h3>";
		}
		if($tag == "lien")
		{
			echo "<a href='".htmlspecialchars($attr['url'])."'>";
		}
		

	}

	/*
	** Calback startTag find in XML
	*/
	function endTag($sax,$tag)
	{
		if($tag == "Recette")
		{
			echo "</h1>";
		}
		if($tag == "Infos")
		{
			echo "</div>";
		}
		if($tag == "TempsCuisson")
		{
			echo "</h3>";	
		}
		if($tag == "TempsPrepa")
		{
			echo "</h3>";	
		}
		if($tag == "PhotoUrl")
		{
			echo "'>";	
		}
		
		if($tag == "Ingredients")
		{
			echo "</ul>";
		}
		if($tag == "Ingredient")
		{
			echo "</li>";
		}
		if($tag == "Preparation")
		{
			echo "</div>";
		}
		if($tag == "lien")
		{
			echo "<a/>";
		}
	}
	 function tagContent ( $sax, $content )
	 {
	 	echo $content;
 	 }


?>

