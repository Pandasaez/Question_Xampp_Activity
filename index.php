<?php
$host = 'localhost';
$user = 'root'; 
$pass = '';
$dbname = 'questionnaire';
$port = '3307';

$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die("Connection Failed: " .mysqli_connect_error());
}



// Define questions and answers (Original Situation)
// $questions = [
//     [
//         "question" => "What does PHP stand for?",
//         "options" => ["Personal Home Page", "Private Home Page", "PHP: Hypertext Preprocessor", "Public Hypertext Preprocessor"],
//         "answer" => 2
//     ],
//     [
//         "question" => "Which symbol is used to access a property of an object in PHP?",
//         "options" => [".", "->", "::", "#"],
//         "answer" => 1
//     ],
//     [
//         "question" => "Which function is used to include a file in PHP?",
//         "options" => ["include()", "require()", "import()", "load()"],
//         "answer" => 0
//     ]
// ];

// Fetching the Questions from the Database
$questions = [];
$sql = "SELECT * FROM questions";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            "question" => $row['question'],
            "options" => [$row['option1'], $row['option2'], $row['option3'], $row['option4']],
            "answer" => $row['answer']
        ];
    }
} else {
    die("No questions found in the database.");
}

// Initialize score
$score = 0;

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $player_name = ($_POST['player_name']);
    foreach ($questions as $index => $question) {
        if (isset($_POST["question$index"]) && $_POST["question$index"] == $question['answer']) {
            $score++;
        }
    }

    // Save the score to the Leaderboard
    $stmt = $conn->prepare("INSERT INTO leaderboard (player_name, score) VALUES (?, ?)");
    $stmt->bind_param("si", $player_name, $score);
    $stmt->execute();
    $stmt->close();

    // Display score and styled "Try Again" button
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>PHP Quiz - Results</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container text-center mt-5'>
            <div class='alert alert-success' role='alert'>
                <h2>Your Score: <span class='fw-bold'>$score</span> / <span class='fw-bold'>" . count($questions) . "</span></h2>
            </div>
            <a href='index.php' class='btn btn-primary btn-lg mt-4'>Try Again</a>
        </div>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>
    ";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">PHP Quiz</h1>
        <form method="post" action="">
            <div class="mb-3">
                <label for="player_name" class="form-label">Enter Your Name:</label>
                <input type="text" id="player_name" name="player_name" class="form-control" required>
            </div>
            <?php foreach ($questions as $index => $question): ?>
                <fieldset class="mb-4">
                    <legend class="fw-bold"><?php echo $question['question']; ?></legend>
                    <?php foreach ($question['options'] as $optionIndex => $option): ?>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="question<?php echo $index; ?>" value="<?php echo $optionIndex; ?>" id="question<?php echo $index . $optionIndex; ?>">
                            <label class="form-check-label" for="question<?php echo $index . $optionIndex; ?>">
                                <?php echo $option; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-success">Submit</button>
        </form>

        <h2 class="text-center mt-5">Leaderboard</h2>
        <table class="table table-striped table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Rank</th>
                    <th>Player Name</th>
                    <th>Score</th>
                    <th>Date Played</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch top scores from leaderboard
                $sql = "SELECT player_name, score, date_played FROM leaderboard ORDER BY score DESC, date_played ASC LIMIT 10";
                $result = $conn->query($sql);
                $rank = 1;

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $rank++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['player_name']) . "</td>";
                        echo "<td>" . $row['score'] . "</td>";
                        echo "<td>" . $row['date_played'] . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>