<?php
require_once __DIR__.'/../../config/db.php';


if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $price = $_POST['price'];

    // Upload ảnh
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = __DIR__."/../../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = "uploads/" . $fileName;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO courts (name,location,price_per_hour,image_path) VALUES (?,?,?,?)");
    $stmt->execute([$name,$location,$price,$imagePath]);

    echo "Thêm sân thành công!";
}
?>
<form method="post" enctype="multipart/form-data">
  <input name="name" placeholder="Tên sân" required>
  <input name="location" placeholder="Địa điểm">
  <input name="price" type="number" required>
  <input type="file" name="image">
  <button type="submit">Thêm sân</button>
</form>
