<?php
    require __DIR__ . "/../src/bootstrap.php";
    // view("header",['title' => 'Victory Sport']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="\css\style.css?v=<?php echo time();?>"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<a href="admin/login.php">admin</a>
<a href="admin/admin-home.php">admin home</a>
<a href="user/item-select.php">user item select</a>
<a href="user/home-page.php">user home page</a>

<a href="user/cart.php">cart</a>




</body>
</html>


<?php
    view("footer")
?>