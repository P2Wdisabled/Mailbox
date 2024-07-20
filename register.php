<?php
// register.php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $jour_naissance = $_POST['jour_naissance'];
    $mois_naissance = $_POST['mois_naissance'];
    $annee_naissance = $_POST['annee_naissance'];
    $password = hash('sha256', $_POST['password']);
    $domain_id = 1; // assuming 'shipmanager.fr' is domain_id 1

    // Insert into users table
    $sql_users = "INSERT INTO users (nom, prenom, email, jour_naissance, mois_naissance, annee_naissance, password) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_users = $conn->prepare($sql_users);
    $stmt_users->bind_param('sssiiis', $nom, $prenom, $email, $jour_naissance, $mois_naissance, $annee_naissance, $password);

    // Insert into virtual_users table
    $sql_virtual_users = "INSERT INTO virtual_users (domain_id, password, email) VALUES (?, ?, ?)";
    $stmt_virtual_users = $conn->prepare($sql_virtual_users);
    $stmt_virtual_users->bind_param('iss', $domain_id, $password, $email);

    // Insert into virtual_aliases table
    $sql_virtual_aliases = "INSERT INTO virtual_aliases (domain_id, source, destination) VALUES (?, ?, ?)";
    $stmt_virtual_aliases = $conn->prepare($sql_virtual_aliases);
    $stmt_virtual_aliases->bind_param('iss', $domain_id, $email, $email);

    if ($stmt_users->execute() && $stmt_virtual_users->execute() && $stmt_virtual_aliases->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt_users->close();
    $stmt_virtual_users->close();
    $stmt_virtual_aliases->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <form method="post" action="register.php">
        <label for="nom">Nom:</label><br>
        <input type="text" id="nom" name="nom" required><br>
        <label for="prenom">Prenom:</label><br>
        <input type="text" id="prenom" name="prenom" required><br>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>
        <label for="jour_naissance">Day of Birth:</label><br>
        <input type="number" id="jour_naissance" name="jour_naissance" required><br>
        <label for="mois_naissance">Month of Birth:</label><br>
        <input type="number" id="mois_naissance" name="mois_naissance" required><br>
        <label for="annee_naissance">Year of Birth:</label><br>
        <input type="number" id="annee_naissance" name="annee_naissance" required><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Register">
    </form>
</body>
</html>
