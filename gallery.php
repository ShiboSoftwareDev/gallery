<?php
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$theme = isset($_COOKIE["theme"]) ? $_COOKIE["theme"] : "light";

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM images WHERE user_id=$user_id ORDER BY uploaded_at DESC";
$result = $conn->query($sql);

$images = [];
while ($row = $result->fetch_assoc()) {
    if (file_exists($row['filepath'])) {
        $images[] = $row;
    } else {
        $stmt = $conn->prepare("DELETE FROM images WHERE id=?");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: <?php echo $theme == "dark" ? "#333" : "#f0f0f0"; ?>;
            color: <?php echo $theme == "dark" ? "#fff" : "#000"; ?>;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
        }
        .gallery {
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .gallery img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            margin: 10px;
            position: relative;
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: red;
            color: white;
            border: none;
            cursor: pointer;
        }
        .drop-zone {
            width: 100%;
            height: 200px;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            cursor: pointer;
        }
        .drop-zone input {
            display: none;
        }
        .user-page-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .user-page-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <button class="user-page-btn" onclick="window.location.href='user.php'">User Page</button>
    <div class="drop-zone" id="drop-zone">
        Drag and drop images here or click to upload
        <input type="file" id="file-input" multiple accept="image/*">
    </div>
    <div class="gallery">
        <?php foreach ($images as $image): ?>
            <div class="image-container" style="position: relative;">
                <img src="<?php echo $image['filepath']; ?>" alt="Image" class="gallery-image" data-id="<?php echo $image['id']; ?>">
                <button class="delete-btn" data-id="<?php echo $image['id']; ?>">Delete</button>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="fullscreen-container" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); justify-content: center; align-items: center; z-index: 1000;">
        <button id="return-btn" style="position: absolute; top: 20px; left: 20px; background: #007bff; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">← Return</button>
        <img id="fullscreen-image" src="" alt="Full Screen Image" style="max-width: 100%; max-height: 100%; object-fit: contain;">
        <button id="fullscreen-delete-btn" style="position: absolute; top: 20px; right: 20px; background: red; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">Delete</button>
        <button id="arrow-left-btn" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: #007bff; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">←</button>
        <button id="arrow-right-btn" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: #007bff; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">→</button>
    </div>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const fullscreenContainer = document.getElementById('fullscreen-container');
        const fullscreenImage = document.getElementById('fullscreen-image');
        const returnBtn = document.getElementById('return-btn');
        const fullscreenDeleteBtn = document.getElementById('fullscreen-delete-btn');
        const arrowLeftBtn = document.getElementById('arrow-left-btn');
        const arrowRightBtn = document.getElementById('arrow-right-btn');
        let currentImageId = null;
        let currentIndex = 0;
        const images = Array.from(document.querySelectorAll('.gallery-image'));

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragging');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragging');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragging');
            const files = e.dataTransfer.files;
            uploadFiles(files);
        });

        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', () => {
            const files = fileInput.files;
            uploadFiles(files);
        });

        function uploadFiles(files) {
            const formData = new FormData();
            for (const file of files) {
                formData.append('images[]', file);
            }

            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error uploading files');
                }
            });
        }

        images.forEach((image, index) => {
            image.addEventListener('click', () => {
                fullscreenImage.src = image.src;
                fullscreenContainer.style.display = 'flex';
                currentImageId = image.getAttribute('data-id');
                currentIndex = index;
            });
        });

        returnBtn.addEventListener('click', () => {
            fullscreenContainer.style.display = 'none';
        });

        fullscreenDeleteBtn.addEventListener('click', () => {
            deleteImage(currentImageId);
        });

        arrowLeftBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            fullscreenImage.src = images[currentIndex].src;
            currentImageId = images[currentIndex].getAttribute('data-id');
        });

        arrowRightBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % images.length;
            fullscreenImage.src = images[currentIndex].src;
            currentImageId = images[currentIndex].getAttribute('data-id');
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', () => {
                const imageId = button.getAttribute('data-id');
                deleteImage(imageId);
            });
        });

        function deleteImage(id) {
            fetch('delete_image.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting image');
                }
            });
        }
    </script>
</body>
</html>
