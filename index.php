
<!doctype html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Marmiton crawler</title>
	<link rel="stylesheet" href="css/style.css">
	<link relf="stylesheeat" href="css/reset.css">
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
	<script type="text/javascript" src="js/ajax.js"></script>
</head>
<body>
	<section id="containerForm">
		<h2>Url de la recette</h2>
		<form id="monForm"  action="script/getRecipe.php" method="POST">
			<div id="containerInput">
			<input type="text" name="recipeLink" placeholder="Entrer un lien de recette">
			<input type="submit" value="Analyser" >
			</div>
		</form>
	</section>
	<div id="waiting"></div>
	<section id="result"></section>
</body>
</html>