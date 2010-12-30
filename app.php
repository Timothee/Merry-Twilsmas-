<?php



$sms_body = $_REQUEST['Body'];
$from = $_REQUEST['From'];

$db = new PDO("sqlite:twilsmas.db");


switch (strtolower(substr($sms_body, 0, strpos($sms_body, ' ')))) {
  case 'idea':
    $wish = substr($sms_body, strpos($sms_body, ' ') + 1);
    $hash = substr(md5($wish.time().$from), 0, 4);
    $query = "INSERT INTO Wishes (Number, Wish, hash, Status) VALUES ('$from', '$wish', '$hash', '0')";
    $db->exec($query);
    $reply_body = '"'.$wish.'" added to your wishlist.';
    break;

  case 'wishlist':
  case 'list':
    $recipient = substr($sms_body, strpos($sms_body, ' ') + 1);
    $query = "SELECT * FROM Wishes WHERE Status = '0' AND Number = '$recipient'";
    
    $reply_body = "Wishlist for $recipient:\n";
    $i = 1;
    foreach($db->query($query) as $row) {
      $reply_body .= "$i. ".$row['Wish']." (".$row['hash'].")\n";
      $i++;
    } // foreach
    
    if ($i == 1) {
      $reply_body = "$recipient doesn't have a wishlist yet. But what do *you* want? Reply 'idea [your idea here]'!";
    } // if
    break;

  case 'bought':
    $hash = substr($sms_body, strpos($sms_body, ' ') + 1, 4);
    $query = "UPDATE Wishes SET Status = '1' WHERE hash = '$hash'";
    $db->exec($query);
    $reply_body = 'Thanks! And Merry Twilsmas!';
    break;
  
  case 'help':
  default:
    $reply_body = "Add to your list: 'idea [your idea]'\n".
                  "Get someone's list: 'list +14155551234'\n".
                  "Check off item: 'bought ' + item's 4-char code\n".
                  "Try 'list +1732440XMAS' now!";
    break;
} // switch

?>
<?php
    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<Response>
    <Sms><?php echo $reply_body ?></Sms>
</Response>
