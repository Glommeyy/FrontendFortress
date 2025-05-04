<?php
session_start();
include 'database.php'; 

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT gold FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT COUNT(*) AS locked_slots FROM quest_slots WHERE user_id = ? AND status = 'locked'");
    $stmt->execute([$user_id]);
    $lockedSlots = $stmt->fetch(PDO::FETCH_ASSOC)['locked_slots'];

    $stmt = $pdo->prepare("SELECT users.name, user_profiles.level, user_profiles.exp, user_profiles.reward_coins, user_profiles.gold 
                       FROM users 
                       JOIN user_profiles ON users.id = user_profiles.user_id 
                       WHERE users.id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SHOP</title>
  <link rel="stylesheet" href="css/homepage.css" />
  <link rel="stylesheet" href="css/shop.css" />
  <link rel="stylesheet" href="css/conversion.css" />
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");
    @import url("https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oxanium:wght@200..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap");
    @import url("https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oxanium:wght@200..800&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap");
    @import url("https://fonts.googleapis.com/css2?family=Michroma&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oxanium:wght@200..800&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap");
  </style>
</head>

<body>
  <div class="header">
    <div class="leftnav">
      <div class="prof-pic">
        <img src="pic/profpic.png" alt="" />
      </div>
      <div class="ign-level">
        <span class="ign"><?php echo htmlspecialchars($user['name']); ?></span>
        <span class="lvl">LVL.69</span>
      </div>
      <div class="expbar">
        <span><img src="pic/progbar.png" alt="" /></span>
      </div>
    </div>
    <img
      src="pic/title.png"
      alt=""
      class="brand"
      style="width: 803px; height: 335px; top: -50px" />
    <div class="rightnav">
      <div class="home" onclick="backHome()">
      <a style="text-decoration: none;" href="homepage.php"><img src="pic/home 1.png" alt="" class="home-icon" /></a>
        <a style="text-decoration: none;" href="homepage.php"><span id="home-text">HOME</span></a>
      </div>
      <div class="logout" onclick="   ">
        <img src="pic/Exit.png" alt="" class="logout-icon" />
        <span class="logout-txt">LOGOUT</span>
      </div>
    </div>
  </div>

  <div id="container">
  </div>

  <div id="shopContainer">
    <div id="itemContainer_1">
      <h1>Experience Points<br>Bomb</h1>
      <div id="item-holder">
        <img src="pic/Potion.png" alt="" class="Potions">
      </div>
      <div class="item-price">
        <img src="pic/gold coins.png" alt="" class="gold-coins">
        <span id="price">70</span>
      </div>
      <form action="" method="POST">
        <button id="buyButton" type="submit">BUY</button>
      </form>
    </div>
    <div id="itemContainer_2">
      <h1>Quest Slot</h1>
      <div id="item-holder">

      </div>
      <div class="item-price">
        <img src="pic/gold coins.png" alt="" class="gold-coins">
        <span id="price">100</span>
      </div>
      <div class="shop-slots">
        <?php if ($lockedSlots > 0): ?>
        <form action="unlock_slot.php" method="POST">
          <input type="hidden" name="task_id" value="">
          <button id="buyButton" type="submit">Unlock Slot</button>
          <?php else: ?>
            <p style="margin-top: 17px;" class="text-success">All quest slots are unlocked!</p>
          <?php endif; ?>
        </form>
      </div>
    </div>
    <div id="bodyContainer">
      <div id="convertContainer">
        <h3>Convert</h3>
        <div id="RewardCoinsCointainer">
          <form action="" method="POST">
            <input type="number" inputmode="numeric" name="rewardcoins" min="1" required id="rewardcoinsAmount">
        </div>
        <div id="GoldCoinsContainer">
          <img src="pic/exp coins.png" alt="" class="ConverterPic"><span class="convertervalue" id="price">1000</span><span id="equals">=</span>
          <img src="puc/gold coins.png" alt="" class="ConverterPic"><span class="convertervalue" id="goldcon">1</span></span>
        </div>
        <br>
        <p id="conversionstatus"></p>
        <div id="conversionbuttonscontainer">
          <button id="resetconversion" type="reset">RESET</button>
          <button id="convertbutton" type="submit">CONVERT</button>
        </div>
        </form>
      </div>
      <div id="cashoutContainer">
        <h3 class="ch">CASHOUT</h3>
        <div class="info-container">
          <span class="info-icon">ℹ️</span>
          <div class="info-text" id="cashoutparagraph">10 Gold ≈ PHP 10</div>
        </div>
        <div id="cashoutamountcontainer">
          <form action="" method="POST">
            <div>
              <label for="gold_requested">Gold Requested:</label>
              <input type="number" name="gold_requested" id="cashoutamount" required>
            </div>
            <div>
              <label for="payment_method">Payment Method:</label>
              <select id="cashoutamount" name="payment_method" id="payment_method" required>
                <option value="gcash">GCash</option>
                <option value="paymaya">PayMaya</option>
                <option value="bank_transfer">Bank Transfer</option>
              </select>
            </div>
            <div>
              <label for="mobile_number">Mobile Number:</label>
              <input type="text" name="mobile_number" id="cashoutamount" required>
            </div>
            <div id="cashoutbuttoncontainer">
              <button type="submit" id="cashoutbutton" onclick="cashoutRequest()">CASHOUT</button>
            </div>
          </form>
        </div>
        <br>
        <p id="cashoutstatus"></p>
      </div>
    </div>
  </div>

  <div id="prompt">
    <div id="promptContent">
      <div id="closepromptbutton" onclick="closePrompt()">X</div>
      <h1>Succesful Purchase!</h1>
      <p>The item has been bought succesfully!</p>
    </div>
  </div>

  <div id="errorprompt">
    <div id="errorpromptContent">
      <div id="errorclosepromptbutton" onclick="closePrompt()">X</div>
      <h1>Purchase Limit!!</h1>
      <p>All quests slot has been purchased!</p>
    </div>
  </div>
</body>
<script>
</script>
<script src="javascript/shop.js"></script>
<script src="javascript/convert.js"></script>

</html>