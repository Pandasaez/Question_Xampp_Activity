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
echo "Connected Successfully!";



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

    // Saving the score to the LEaderBaord
    $stmt = $conn->prepare("INSERT INTO leaderboard (player_name, score) VALUES (?, ?)");
    $stmt->bind_param("si", $player_name, $score);
    $stmt->execute();
    $stmt->close();

    // Display score
    echo "<h2>Your Score: $score/" . count($questions) . "</h2>";
    echo '<a href="index.php">Try Again</a>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Quiz</title>
</head>
<body>
    <h1>PHP Quiz</h1>
    <form method="post" action="">
        <label for="player_name">Enter Your Name:</label>
        <input type="text" id="player_name" name="player_name" required><br><br>
        <?php foreach ($questions as $index => $question): ?>
            <fieldset>
                <legend><?php echo $question['question']; ?></legend>
                <?php foreach ($question['options'] as $optionIndex => $option): ?>
                    <label>
                        <input type="radio" name="question<?php echo $index; ?>" value="<?php echo $optionIndex; ?>">
                        <?php echo $option; ?>
                    </label><br>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
        <input type="submit" value="Submit">
    </form>

    <h2>Leader Board</h2>
    <table border="1">
        <tr>
            <th>Rank</th>
            <th>Player Name</th>
            <th>Score</th>
            <th>Date Played</th>
        </tr>
        <?php
        // Fetch top scores from leaderboard
        $sql = "SELECT player_name, score, date_played FROM leaderboard ORDER BY score DESC, date_played ASC LIMIT 10";
        $result = $conn->query($sql);
        $rank = 1;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $rank++ . "</td>";
                echo "<td>" . ($row['player_name']) . "</td>";
                echo "<td>" . $row['score'] . "</td>";
                echo "<td>" . $row['date_played'] . "</td>";
                echo "</tr>";
            }
        } 
        ?>
    </table>
</body>
</html>

<?php
$conn->close();
?>
