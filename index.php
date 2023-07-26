<?php
// Pour Connexion à la base de données 
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "php1";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Pour Fonction pour récupérer tous les produits avec le nom de la catégorie
function getProducts($conn)
{
    $stmt = $conn->query("SELECT p.*, r.idCategory FROM product p LEFT JOIN productretail r ON p.idCategory = r.idCategory");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as &$product) {
        $product['category'] = getCategoryNameById($conn, $product['idCategory']);
    }

    return $products;
}

// Pour Fonction pour ajouter un nouveau produit
function addProduct($conn, $reference, $description, $priceTaxIncl, $priceTaxExcl, $quantity, $idCategory)
{
    $stmt = $conn->prepare("INSERT INTO product (reference, description, priceTaxIncl, priceTaxExcl, quantity, idCategory) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$reference, $description, $priceTaxIncl, $priceTaxExcl, $quantity, $idCategory]);
}

// Pour Fonction pour mettre à jour un produit existant
function updateProduct($conn, $idProduct, $reference, $description, $priceTaxIncl, $priceTaxExcl, $quantity, $idCategory)
{
    $stmt = $conn->prepare("UPDATE product SET reference = ?, description = ?, priceTaxIncl = ?, priceTaxExcl = ?, quantity = ?, idCategory = ? WHERE idProduct = ?");
    $stmt->execute([$reference, $description, $priceTaxIncl, $priceTaxExcl, $quantity, $idCategory, $idProduct]);
}

// Pour Fonction pour supprimer un produit
function deleteProduct($conn, $idProduct)
{
    $stmt = $conn->prepare("DELETE FROM product WHERE idProduct = ?");
    $stmt->execute([$idProduct]);
}

// Pour Fonction pour récupérer le nom de la catégorie par son idCategory
function getCategoryNameById($conn, $idCategory)
{
    $stmt = $conn->prepare("SELECT category FROM productretail WHERE idCategory = ?");
    $stmt->execute([$idCategory]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return $result['category'];
    } else {
        return 'N/A';
    }
}

// Pour Fonction pour ajouter une nouvelle catégorie de produits
function addProductRetail($conn, $category)
{
    $stmt = $conn->prepare("INSERT INTO productretail (category) VALUES (?)");
    $stmt->execute([$category]);
}

// Pour Fonction pour supprimer une catégorie de produits
function deleteProductRetail($conn, $idCategory)
{
    $stmt = $conn->prepare("DELETE FROM productretail WHERE idCategory = ?");
    $stmt->execute([$idCategory]);
}

// Pour Fonction pour récupérer toutes les catégories de produits
function getAllCategories($conn)
{
    $stmt = $conn->query("SELECT * FROM productretail");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pour Fonction pour afficher les catégories sous forme de tableau
function displayCategoriesTable($categories)
{
    if (empty($categories)) {
        echo "Aucune catégorie trouvée.";
        return;
    }

    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Catégorie</th></tr>";

    foreach ($categories as $category) {
        echo "<tr>";
        echo "<td>" . $category['idCategory'] . "</td>";
        echo "<td>" . $category['category'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
}

// Pour Gérer les opérations CRUD
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["addProduct"])) {
        $reference = $_POST["reference"];
        $description = $_POST["description"];
        $priceTaxIncl = $_POST["priceTaxIncl"];
        $priceTaxExcl = $_POST["priceTaxExcl"];
        $quantity = $_POST["quantity"];
        $idCategory = $_POST["idCategory"];
        addProduct($conn, $reference, $description, $priceTaxIncl, $priceTaxExcl, $quantity, $idCategory);
    } elseif (isset($_POST["updateProduct"])) {
        $idProduct = $_POST["idProduct"];
        $reference = $_POST["reference"];
        $description = $_POST["description"];
        $priceTaxIncl = $_POST["priceTaxIncl"];
        $priceTaxExcl = $_POST["priceTaxExcl"];
        $quantity = $_POST["quantity"];
        $idCategory = $_POST["idCategory"];
        updateProduct($conn, $idProduct, $reference, $description, $priceTaxIncl, $priceTaxExcl, $quantity, $idCategory);
    } elseif (isset($_POST["deleteProduct"])) {
        $idProduct = $_POST["idProduct"];
        deleteProduct($conn, $idProduct);
    } elseif (isset($_POST["addCategory"])) {
        $category = $_POST["category"];
        addProductRetail($conn, $category);
    }
}

// Pour Gérer les opérations CRUD
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["addProduct"])) {
    } elseif (isset($_POST["updateProduct"])) {
    } elseif (isset($_POST["deleteProduct"])) {
    } elseif (isset($_POST["addCategory"])) {
        $category = $_POST["category"];
        addProductRetail($conn, $category);
    } elseif (isset($_POST["deleteCategory"])) {
        $idCategoryToDelete = $_POST["idCategoryToDelete"];
        deleteProductRetail($conn, $idCategoryToDelete);
    }
}

// Pour Récupérer tous les produits
$products = getProducts($conn);

// Pouer Récupérer toutes les catégories
$categories = getAllCategories($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Interface CRUD - Produits</title>
</head>
<body>
    <h1>Liste des Produits</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Référence</th>
            <th>Description</th>
            <th>Prix TTC</th>
            <th>Prix HT</th>
            <th>Quantité</th>
            <th>Catégorie</th>
            <th>Action</th>
        </tr>
        <?php foreach ($products as $product) : ?>
            <tr>
                <td><?= $product['idProduct']; ?></td>
                <td><?= $product['reference']; ?></td>
                <td><?= $product['description']; ?></td>
                <td><?= $product['priceTaxIncl']; ?></td>
                <td><?= $product['priceTaxExcl']; ?></td>
                <td><?= $product['quantity']; ?></td>
                <td><?= $product['category']; ?></td>
                
                <td>
                    <form method="post">
                        <input type="hidden" name="idProduct" value="<?= $product['idProduct']; ?>">
                        <button type="submit" name="deleteProduct">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h2>Ajouter un Produit</h2>
    <form method="post">
        <label>Référence:</label>
        <input type="text" name="reference" required><br>

        <label>Description:</label>
        <input type="text" name="description" required><br>

        <label>Prix TTC:</label>
        <input type="number" name="priceTaxIncl" required><br>

        <label>Prix HT:</label>
        <input type="number" name="priceTaxExcl" required><br>

        <label>Quantité:</label>
        <input type="number" name="quantity" required><br>

        <label>Catégorie (ID):</label>
        <input type="number" name="idCategory" required><br>

        <button type="submit" name="addProduct">Ajouter</button>
    </form>

    <h2>Modifier un Produit</h2>
    <form method="post">
        <label>ID du produit à modifier:</label>
        <input type="number" name="idProduct" required><br>

        <label>Nouvelle référence:</label>
        <input type="text" name="reference" required><br>

        <label>Nouvelle description:</label>
        <input type="text" name="description" required><br>

        <label>Nouveau prix TTC:</label>
        <input type="number" name="priceTaxIncl" required><br>

        <label>Nouveau prix HT:</label>
        <input type="number" name="priceTaxExcl" required><br>

        <label>Nouvelle quantité:</label>
        <input type="number" name="quantity" required><br>

        <label>Nouvelle catégorie (ID):</label>
        <input type="number" name="idCategory" required><br>

        <button type="submit" name="updateProduct">Modifier</button>
    </form>

    <h2>Supprimer un Produit</h2>
    <form method="post">
        <label>ID du produit à supprimer:</label>
        <input type="number" name="idProduct" required><br>

        <button type="submit" name="deleteProduct">Supprimer</button>
    </form>

    <h2>Ajouter une Catégorie</h2>
    <form method="post">
        <label>Nouvelle catégorie:</label>
        <input type="text" name="category" required><br>
        <button type="submit" name="addCategory">Ajouter</button>
    </form>

    <h2>Liste des Catégories</h2>
    <?php displayCategoriesTable($categories); ?>

    <h2>Supprimer une Catégorie</h2>
    <form method="post">
        <label>ID de la catégorie à supprimer:</label>
        <input type="number" name="idCategoryToDelete" required><br>
        <button type="submit" name="deleteCategory">Supprimer</button>
    </form>
    
</body>
</html>
