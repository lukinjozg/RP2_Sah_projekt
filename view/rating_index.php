<?php require_once __DIR__ . '/_header.php'; ?>

<?php 
	echo $title.'<br>';
?>

<canvas id="myCanvas" width="400" height="200"></canvas>

    <script>
        // Read data from PHP using JSON
        var data = <?php echo json_encode($ratingList); ?>;

        // Get the canvas element and its context
        var canvas = document.getElementById('myCanvas');
        var ctx = canvas.getContext('2d');

        ctx.fillStyle = "lightblue";

        // Draw a filled rectangle covering the entire canvas area
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Calculate graph dimensions
        var graphWidth = canvas.width - 20;
        var graphHeight = canvas.height - 20;

        // Calculate dot positions and draw dots
        var dotSize = 6; // Adjust the dot size as needed
        var dotSpacing = graphWidth / (data.length - 1);
        for (var i = 0; i < data.length; i++) {
            var x = i * dotSpacing + 10;
            var y = canvas.height - ((data[i] / Math.max(...data)) * graphHeight) - 10;

            ctx.fillStyle = 'blue';
            ctx.fillRect(x - dotSize / 2, y - dotSize / 2, dotSize, dotSize);
        }

        for (var i = 0; i < data.length; i++) {
            var x = i * dotSpacing + 10;
            var y = canvas.height - ((data[i] / Math.max(...data)) * graphHeight) - 10;

            ctx.font = "10px Arial";

            ctx.fillStyle = 'blue';

            ctx.fillText(data[i], x - 10, y + 12);
        }

        // Draw connecting lines
        ctx.strokeStyle = 'black';
        ctx.beginPath();
        ctx.moveTo(10, canvas.height - ((data[0] / Math.max(...data)) * graphHeight) - 10);
        for (var i = 1; i < data.length; i++) {
            var x = i * dotSpacing + 10;
            var y = canvas.height - ((data[i] / Math.max(...data)) * graphHeight) - 10;

            ctx.lineTo(x, y);
        }
        ctx.stroke();
    </script>

<?php require_once __DIR__ . '/_footer.php'; ?>
