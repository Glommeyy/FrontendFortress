<?php
session_start();
include 'database.php'; // Ensure this file contains the PDO connection as $pdo

// Redirect if not logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Fetch user profile data
try {
    $stmt = $pdo->prepare("SELECT users.name, user_profiles.level, user_profiles.exp, user_profiles.reward_coins, user_profiles.gold 
                           FROM users 
                           JOIN user_profiles ON users.id = user_profiles.user_id 
                           WHERE users.id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "User profile not found.";
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: login.php");
    exit();
}

// Fetch all quests
$quests = [];
try {
$stmt = $pdo->query("SELECT quests.id, quests.title, quests.short_description, quests.full_description, quests.difficulty, quests.user_id, users.name AS posted_by 
                     FROM quests 
                     JOIN users ON quests.user_id = users.id 
                     ORDER BY quests.created_at DESC");
    $quests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

// Fetch quest slots
$slots = [];
try {
    $stmt = $pdo->prepare("SELECT qs.slot_number, qs.quest_title, qs.status, q.id AS quest_id 
                           FROM quest_slots qs
                           LEFT JOIN quests q ON qs.quest_title = q.title
                           WHERE qs.user_id = ? 
                           ORDER BY qs.slot_number ASC");
    $stmt->execute([$user_id]);
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>

<body lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Homepage</title>
        <link rel="stylesheet" href="css/das.css">
        <link rel="stylesheet" href="css/modal.css">
        <link rel="stylesheet" href="css/postmoda.css">
        <link rel="stylesheet" href="css/reportmoda.css">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oxanium:wght@200..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oxanium:wght@200..800&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
            @import url('https://fonts.googleapis.com/css2?family=Michroma&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oxanium:wght@200..800&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
        </style>
    </head>

    <body>

        <div class="header">
            <div class="leftnav">
                <div class="prof-pic">
                    <img src="pic/profpic.png" alt="">
                </div>
                <div class="ign-level">
                    <span class="ign"><?php echo htmlspecialchars($user['name']); ?></span>
                    <span class="lvl">LVL <?php echo $user['level']; ?></span>
                </div>
                <div class="expbar" style="width: 200px; height: 30px; background-color: #A5A5A5; border-radius: 32px; overflow: hidden; margin-left: 10px;">
                    <div style="border-radius: 32px; height: 100%; width: <?php echo min(100, ($user['exp'] % 100)); ?>%;  background: linear-gradient(90deg, #000000 0%, #210D26 25%, #291D2C 50%, #632673 75%, #84319B 100%); border: 1px solid #FFFFFF;"></div>
                </div>
            </div>
            <img src="pic/title.png" alt="" class="brand">
            <div class="rightnav">
                <div class="currency">
                    <img src="pic/exp coins.png" alt="" class="exp-coins">
                    <span class="exp-amnt"><?php echo $user['reward_coins'] !== null ? $user['reward_coins'] : 0; ?></span>
                    <img src="pic/gold coins.png" alt="" class="gold-coins">
                    <span class="gold-amnt"><?php echo $user['gold']; ?></span>
                </div>
                <div id="report">
                    <img src="pic/Warning.png" alt="">
                    <p>Report</p>
                </div>
                <div class="logout">
                    <a href="logout.php"><img src="pic/Exit.png" alt="" class="logout-icon"></a>
                    <a style="text-decoration: none;" href="logout.php"><span class="logout-txt">LOGOUT</span></a>
                </div>
            </div>
        </div>





        <div class="body-container">
    <div class="leftside-container">
        <div class="left-title">PENDING TASKS</div>
        <?php foreach ($slots as $slot): ?>
            <div class="task-cont" id="task-cont">
                <div class="slot-1">
                    <img src="" alt="">
                    <?php if ($slot['status'] === 'available' && empty($slot['quest_title'])): ?>
                        <span class="text-success"></span>
                    <?php elseif ($slot['status'] === 'occupied' && !empty($slot['quest_id'])): ?>
                        <a href="quest_details.php?id=<?= urlencode($slot['quest_id']) ?>" style="text-decoration: none; color: inherit;">
                            <h3><?= htmlspecialchars($slot['quest_title']) ?></h3>
                        </a>
                    <?php else: ?>
                        <span class="text-danger"><img src="pic/Lock.png" alt=""></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

            <div class="middle-container">
                <div class="holder">
                    <div class="quest-title">QUEST CARDS</div>
                    <div class="quests-bg">
                        <div class="carousel" id="card-carousel">
                            <?php foreach ($quests as $quest): ?>
                            <div class="card">
                                <div class="card-image">
                                    <div class="quest-price">
                                        <img src="pic/exp coins.png" alt="">
                                        <span id="price"><?php echo htmlspecialchars($quest['difficulty']); ?></span>
                                    </div>
                                    <img src="pic/bbg.jpg" alt="" id="image-holder">
                                </div>
                                <div class="card-desc">
                                    <p class="card-description"><?php echo htmlspecialchars($quest['short_description']); ?></p>
                                </div>
                                <button class="cardbtn" id="card-btn" onclick="openModal('<?php echo $quest['id']; ?>')">More Info</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="new-quest-card">
                        <button class="custom-btn" onclick="openQuestModal()">New Quest</button>
                    </div>
                </div>
            </div>





            <div class="rightside-container">
                <div class="btns">
                    <div class="btn">
                        <a href="inbox.php"><img src="pic/inbox.png" alt=""></a>
                        <a style="text-decoration: none;" href="inbox.php"><span class="logout-txt">INBOX</span></a>
                    </div>
                    <div class="btn">
                        <a href="shop.php"><img src="pic/shopping cart.png" alt=""></a>
                        <a style="text-decoration: none;" href="shop.php"><span>SHOP</span></a>
                    </div>
                </div>
                <div class="leadboards">
                    <h1>LEADERBOARDS</h1>
                    <div class="list">

                    </div>
                </div>
            </div>
        </div>

    </body>

    <!-- Add it here -->
        <div id="questModalContainer" class="custom-modal">
        <div class="custom-modal-content">
            <div class="custom-modal-header">

                <p class="creat">Create a Quest</p>
            </div>
            <div class="custom-modal-body">
                <form action="post_quest.php" method="POST">
                    <div class="cont">
                        <div class="post-left">
                            <div class="custom-form-group1">
                                <label class="lab" for="questTitle">Quest Title</label>
                                <input class="tit" type="text" id="questTitle" name="title" required>
                            </div>
                            <div class="custom-form-group1">
                                <label class="lab" for="questFullDesc">Full Description</label>
                                <textarea class="ful-desc" id="questFullDesc" name="full_description" rows="3" required></textarea>
                            </div>
                        </div>                 

                        <div class="post-right">            
                            <div class="custom-form-group2">
                                <label class="lab" for="questShortDesc">Short Description</label>
                                <textarea class="short-desc" type="text" id="questShortDesc" name="short_description" required></textarea>
                            </div>
                            <div class="custom-form-group3">
                                <label class="lab" for="questDifficulty">Difficulty</label>
                                <select id="questDifficulty" name="difficulty" required>
                                    <option value="Easy">Easy</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Hard">Hard</option>
                                </select>
                            </div>
                            <div class="post-buttons">
                                <button onclick="closeQuestModal()" class="closer">CANCEL</button>
                                <button type="submit">POST</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- quest modal -->
    <?php foreach ($quests as $quest): ?>
    <div id="questModal<?php echo $quest['id']; ?>" tabindex="-1" aria-labelledby="questModalLabel" aria-hidden="true" class="modal" style="display: none;">
        <div class="modal-content">
            <div id="content-left">
                <div id="commentModalContent">
                    <h2 id="commentTitle">COMMENTS</h2>
                    <div id="comments-container">

                        <!-- {{-- Comments Display --}} -->

                    </div>
                </div>

            </div>
            <div id="content-rightt">
                <div class="quest_title">
                    <div class="user">
                        <img src="pic/profpic.png" alt="">
                        <p><span id="modal-posted-by"></span></p>
                    </div>
                    <h2 id="modal-title" class="title" data-title="{{ $quest->title }}"><?php echo htmlspecialchars($quest['title']); ?></h2>
                </div>



                <div class="description-container">
                    <div class="disc_box">
                        <h2 class="discription">DESCRIPTION</h2>
                        <div id="modal-description"><?php echo nl2br(htmlspecialchars($quest['full_description'])); ?></div>
                    </div>
                </div>
                <div id="bottomSection">
                    <div id="leftsection">
                        <h1 class="com">COMMENTS</h1>
                        <div id="leftsectioncontent">

                            <!-- {{-- Form where to get comments --}} -->

                            <form action='' method="POST" id="commentForm">

                                <div class="player-profile" style="display: none">
                                    <img src="pic/profpic.png" alt="">
                                    <span class="player-ign"> KATARINA </span>
                                    <span name="quest_title" id="modal-title" value=""></span>
                                    <input type="hidden" name="quest_id" id="modal-id" value="">
                                </div>
                                <textarea type="text" name="user-comment" id="user-comment" placeholder="Type your comment here.."></textarea>
                                <button type="submit" class="send-icon">
                                    &#x27A4;
                                </button>
                            </form>


                        </div>
                    </div>
                    <div id="rightsection">
                        <div class="rightsectioncontent">
                            <div class="quest-pay">
                                <img src="pic/exp coins.png" alt="" class="exp-coins">
                                <span class="PriceAmount" id="modal-reward-coins"><?php echo htmlspecialchars($quest['difficulty']); ?></span>
                            </div>
                            <div class="accept">
                                <button id="back-btn" type="button" class="close" onclick="closeModal('<?php echo $quest['id']; ?>')">BACK</button>

                                <?php if ($quest['user_id'] == $user_id): ?>
                                    <form action="delete_quest.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this quest?');">
                                        <input type="hidden" name="quest_id" value="<?php echo $quest['id']; ?>">
                                        <button type="submit" class="btn btn-danger" id="delete-btn">DELETE</button>
                                    </form>
                                <?php else: ?>
                                    <!-- Show ACCEPT button for others -->
                                    <form action="accept_quest.php" method="POST">
                                        <input type="hidden" name="quest_id" value="<?php echo $quest['id']; ?>">
                                        <button type="submit" class="btn btn-primary accept-quest-btn" id="accept-btn">ACCEPT</button>
                                    </form>
                                <?php endif; ?>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <p style="display: none"><strong>Difficulty:</strong> <span id="modal-difficulty"></span></p>
        <p style="display: none"><strong>Experience Points:</strong> <span id="modal-exp"></span></p>
    </div>
    <?php endforeach; ?>

    <div id="reportmodal">
        <div id="reportmodalcontent">
            <p id="reportclosebtn">X</p>
            <div style="margin: 0px 0px 20px 0px;">
                <h1 id="reportmodalheader">Report Your Concerns</h1>
                <p id="reportmodalsubheading">Report issues affecting your experience.</p>
            </div>
            <div style="margin: 40px 0px 30px 0px;">
                <label for="reportedplayer" id="reportedplayerlabel">Player In-Game Name:</label>
                <input type="text" name="reportedplayer" id="reportedplayer">
            </div>
            <div style="margin: 40px 0px 20px 0px;">
                <label for="reportreason" id="reportreasonlabel">Add further details to the report.</label> <br>
                <textarea name="reportreason" id="reportreason"></textarea>
            </div>
            <button type="submit" id="reportsubmitbtn">Submit Report</button>
        </div>
    </div>

    <script>
        function closeModal(questId) {
            let modal = document.getElementById("questModal" + questId);
            if (modal) {
                modal.style.display = "none";
            }
        }

        function openModal(questId) {
            let modal = document.getElementById("questModal" + questId);
            if (modal) {
                modal.style.display = "block";
            }
        }
    </script>

    <script>
        // Function to open the modal
        function openQuestModal() {
            document.getElementById("questModalContainer").style.display = "block";
        }

        // Function to close the modal
        function closeQuestModal() {
            document.getElementById("questModalContainer").style.display = "none";
        }

        // Close modal if the user clicks outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById("questModalContainer")) {
                closeQuestModal();
            }
        };

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>