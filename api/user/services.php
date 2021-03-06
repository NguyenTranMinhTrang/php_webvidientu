<?php
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    require_once('../../db/dbhelper.php');
    require_once('../../utils/utility.php');
    function checkUser($email){
        $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
        $user = executeResult($sql, true);
        if(!$user){
            return false;
        }else{
            return true;
        }
    }

    function checkUserByEmailAndPhone($email, $phone) {
        $sql = "SELECT * FROM `users` WHERE `email` = '$email' and `phone` = '$phone'";
        $user = executeResult($sql, true);
        if(!$user){
            return null;
        }else{
            return $user;
        }
    }

    function getDateForDatabase($date){
        $timestamp = strtotime($date);
        $date_formated = date('Y-m-d', $timestamp);
        return $date_formated;
    }

    function getDateTimeForDatabase($date){
        $timestamp = strtotime($date);
        $date_formated = date('Y-m-d H:i:s', $timestamp);
        return $date_formated;
    }

    function getNowDateTime() {
        $date = new DateTime();
        return $date->format('Y-m-d H:i:s');
    }

    function getNowDate(){
        $date = new DateTime();
        return $date->format('Y-m-d');
    }

    function checkCard($cardnumber,$expdate,$cvv){
        $expdateformat = getDateForDatabase($expdate);
        $sql = "SELECT * FROM `debidcard` WHERE `cardnumber` = '$cardnumber' AND `expdate` = '$expdateformat' AND `cvv` = '$cvv'";
        $card = executeResult($sql, true);
        if ($card == null) {
            return false;
        }
        return true;
    }

    function checkReceiver($receiver){
        $sql = "SELECT * FROM `users` WHERE `phone` = '$receiver'";
        $user = executeResult($sql, true);
        if ($user == null) {
            return false;
        }
        return true;
    }

    function getNextIncrement($table) {
        $next_increment = 1;
        $sql = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'cuoiki' AND TABLE_NAME = '$table'";
        $auto_increment = executeResult($sql, true);
        if(!empty($auto_increment)){
            $next_increment = $auto_increment['AUTO_INCREMENT'];
        }
        return $next_increment;
    }

    function getIdIncrement($num,$type) {
        return $type.str_pad($num, "6", "0", STR_PAD_LEFT);
    }
   
    function depositHistory($email,$cardnumber,$expdate,$cvv,$amount){
        $next_increment = getNextIncrement('deposit');
        $id_increment = getIdIncrement($next_increment,'DE');
        $date_time = getNowDateTime();
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`) VALUES ('$id_increment', 'deposit', '$email','$date_time' ,'$amount', 1)";
        execute($sql);
        $sql = "INSERT INTO `deposit` (`idtrans`, `cardnumber`) VALUES ('$id_increment', '$cardnumber')";
        execute($sql);
    }  

    function deposit($email,$cardnumber,$expdate,$cvv,$amount) {
        if(!checkCard($cardnumber,$expdate,$cvv)){
            die (json_encode(array('code' => 1, 'data' => 'Th??ng tin th??? kh??ng h???p l???')));
        }
        if ($cardnumber == 333333){
            die(json_encode(array('code' => 1, 'data' => 'Th??? h???t ti???n')));
        }
        if ($cardnumber == 222222){
            if ($amount > 1000000){
                die(json_encode(array('code' =>1, 'data' => 'S??? ti???n kh??ng ???????c v?????t qu?? 1000000')));
            }
            $sql = "SELECT `balance` FROM `debidcard` WHERE `cardnumber` = '$cardnumber'";
            $balance = executeResult($sql, true);
            if ($balance < $amount){
                die(json_encode(array('code' => 1, 'data' => 'S??? ti???n kh??ng ????? ????? th???c hi???n giao d???ch')));
            }
            $sql = "UPDATE `users` SET `balance` = `balance` + '$amount' WHERE `email` = '$email'";
            execute($sql);
            $sql = "UPDATE `debidcard` SET `balance` = `balance` - '$amount' WHERE `cardnumber` = 222222";
            execute($sql);
            depositHistory($email,$cardnumber,$expdate,$cvv,$amount);
            echo json_encode(array('code' => 0, 'data' => 'N???p ti???n th??nh c??ng'));
        }
        if ($cardnumber == 111111){
            $sql = "UPDATE `users` SET `balance` = `balance` + '$amount' WHERE `email` = '$email'";
            execute($sql);
            $sql = "UPDATE `debidcard` SET `balance` = `balance` - '$amount' WHERE `cardnumber` = 111111";
            execute($sql);
            depositHistory($email,$cardnumber,$expdate,$cvv,$amount);
            echo json_encode(array('code' => 0, 'data' => 'N???p ti???n th??nh c??ng'));
        }
    }

    function withdrawHistory($email,$cardnumber,$expdate,$cvv,$amount,$note,$approval){
        $next_increment = getNextIncrement('withdraw');
        $id_increment = getIdIncrement($next_increment,'WD');
        $date_time = getNowDateTime();
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`) VALUES ('$id_increment', 'withdraw', '$email','$date_time' ,'$amount', '$approval')";
        execute($sql);
        $sql = "INSERT INTO `withdraw` (`idtrans`, `cardnumber`, `note`) VALUES ('$id_increment', '$cardnumber', '$note')";
        execute($sql);
    }

    function withdraw($email,$cardnumber,$expdate,$cvv,$amount,$note){
        if(!checkCard($cardnumber,$expdate,$cvv)){
            die (json_encode(array('code' => 1, 'data' => 'Th??ng tin th??? kh??ng h???p l???')));
        }
        if ($cardnumber == 222222 || $cardnumber == 333333){
            die(json_encode(array('code' => 1, 'data' => 'Th??? n??y kh??ng h??? tr??? ????? r??t ti???n')));
        }
        if ($cardnumber == 111111){
            $date_now = getNowDate();
            $sql = "SELECT * FROM `transactions` WHERE `transtype` = 'withdraw' AND CAST(datetrans AS DATE) = '$date_now'";
            $transactions = executeResult($sql, false);
            if (count($transactions) == 2){
                die(json_encode(array('code' => 1, 'data' => 'B???n ???? r??t ti???n qu?? 2 l???n trong ng??y')));
            }
            $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
            $balance = executeResult($sql, true);
            $fee = $amount*0.05;
            if ($balance['balance'] < ($amount+$fee)){
                die(json_encode(array('code' => 1, 'data' => 'S??? d?? kh??ng ????? ????? th???c hi???n giao d???ch')));
            }
            if ($amount < 5000000){
                $sql = "UPDATE `users` SET `balance` = `balance` - $amount - $fee WHERE `email` = '$email'";
                execute($sql);
                $sql = "UPDATE `debidcard` SET `balance` = `balance` + $amount WHERE `cardnumber` = '$cardnumber'";
                execute($sql);
                withdrawHistory($email,$cardnumber,$expdate,$cvv,$amount,$note,1);
                echo json_encode(array('code' => 0, 'data' => 'R??t ti???n th??nh c??ng'));
            }else{
                withdrawHistory($email,$cardnumber,$expdate,$cvv,$amount,$note,0);
                echo(json_encode(array('code' => 0, 'data' => 'S??? ti???n v?????t qu?? 5000000 c???n ch??? x??t duy???t')));
            }
        }
    }

    function transferHistory($email,$receiver,$amount,$note,$approval,$feepaid){
        $next_increment = getNextIncrement('transfer');
        $id_increment = getIdIncrement($next_increment,'TF');
        $date_time = getNowDateTime();
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`,`receiver`) VALUES ('$id_increment', 'transfer', '$email','$date_time' ,'$amount', '$approval','$receiver')";
        execute($sql);
        $sql = "INSERT INTO `transfer` (`idtrans`,`note`,`feepaid`) VALUES ('$id_increment','$note','$feepaid')";
        execute($sql);
        if($approval == 1){
            sendBalance($receiver,$id_increment);
        }
    }

    function transfer($email,$receiver,$amount,$feepaid,$note){
        $sql = "SELECT `phone` FROM `users` WHERE `email` = '$email'";
        $phone = executeResult($sql, true);
        if($phone['phone'] == $receiver){
            die(json_encode(array('code' => 1, 'data' => 'Kh??ng th??? chuy???n ti???n cho ch??nh m??nh')));
        }
        if ($amount >= 5000000){
            transferHistory($email,$receiver,$amount,$note,0,$feepaid);
            die(json_encode(array('code' => 1, 'data' => 'S??? ti???n v?????t qu?? 5000000 c???n ch??? x??t duy???t')));
        }else{
            if($feepaid == 0){
                $fee = 0.05*$amount;
                $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
                $balance = executeResult($sql, true);
                if ($balance['balance'] < $amount + $fee){
                    die(json_encode(array('code' => 1, 'data' => 'S??? d?? kh??ng ????? ????? th???c hi???n giao d???ch')));
                }
                $sql = "UPDATE `users` SET `balance` = `balance` - $amount - $fee WHERE `email` = '$email'";
                execute($sql);
                $sql = "UPDATE `users` SET `balance` = `balance` + $amount WHERE `phone` = '$receiver'";
                execute($sql);
                transferHistory($email,$receiver,$amount,$note,1,$feepaid);
                echo json_encode(array('code' => 0, 'data' => 'Chuy???n ti???n th??nh c??ng vui l??ng ki???m tra s??? d?? trong t??i kho???n qua email')); 
            }else{
                $fee = 0.05*$amount;
                $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
                $balance = executeResult($sql, true);
                if ($balance['balance'] < $amount){
                    die(json_encode(array('code' => 1, 'data' => 'S??? d?? kh??ng ????? ????? th???c hi???n giao d???ch')));
                }else{
                    $sql = "UPDATE `users` SET `balance` = `balance` - $amount WHERE `email` = '$email'";
                    execute($sql);
                    $sql = "UPDATE `users` SET `balance` = `balance` + $amount - $fee WHERE `phone` = '$receiver'";
                    execute($sql);
                    transferHistory($email,$receiver,$amount,$note,1,$feepaid);
                    echo json_encode(array('code' => 0, 'data' => 'Chuy???n ti???n th??nh c??ng vui l??ng ki???m tra s??? d?? trong t??i kho???n qua email')); 
                }
            }
        } 
    }

    function generateCardCode($networkname) {
        $sql = "SELECT * FROM `network` WHERE `networkname` = '$networkname'";
        $network = executeResult($sql, true);
        if ($network == null) {
            return null;
        }
        $networkid = $network['networkid'];
        $random = rand(10000, 99999);
        return $networkid.$random;
    }

    function topupHistory($email,$networkname,$price,$quantity){
        $next_increment = getNextIncrement('topupcard');
        $id_increment = getIdIncrement($next_increment,'TC');
        $date_time = getNowDateTime();
        $amount = $price * $quantity;
        $sql = "INSERT INTO `transactions` (`idtrans`, `transtype`, `email`,`datetrans`,`amount`, `approval`) VALUES ('$id_increment', 'topupcard', '$email', '$date_time','$amount', 1)";
        execute($sql);
        for ($i = 0; $i<$quantity; $i++){
            $cardcode = generateCardCode($networkname);
            $sql = "INSERT INTO `topupcard` (`idtrans`,`cardcode`, `networkname`, `price`) VALUES ('$id_increment','$cardcode', '$networkname', '$price')";
            execute($sql);
        }
    }

    function topupCard($email,$networkname,$price,$quantity){
        $sql = "SELECT * FROM `network` WHERE `networkname` = '$networkname'";
        $network = executeResult($sql, true);
        if (!$network){
            die(json_encode(array('code' => 1, 'data' => 'Nh?? m???ng kh??ng t???n t???i')));
        }
        $fee =  $network['fee'];
        $sql = "SELECT `balance` FROM `users` WHERE `email` = '$email'";
        $balance = executeResult($sql, true);
        if ($balance['balance'] < ($price*$quantity + $fee*($price*$quantity))){
            die(json_encode(array('code' => 1, 'data' => 'S??? d?? kh??ng ????? ????? th???c hi???n giao d???ch')));
        }else{
            $updateWallet = "UPDATE `users` SET `balance` = `balance` - $price*$quantity - $fee*($price*$quantity) WHERE `email` = '$email'";
            execute($updateWallet);
            topupHistory($email,$networkname,$price,$quantity);
            echo json_encode(array('code' => 0, 'data' => 'Mua th??? th??nh c??ng'));
        }
    }

    function getAllTransactions($email){
        $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
        $user = executeResult($sql, true);
        $phone = $user['phone'];
        $sql = "SELECT * FROM `transactions` WHERE `email` = '$email' OR `receiver` = $phone AND `approval` = 1 ORDER BY `datetrans` DESC";
        $transaction = executeResult($sql, false);
        if (!$transaction){
            die(json_encode(array('code' => 1, 'data' => 'Kh??ng c?? giao d???ch')));
        }
        echo json_encode(array('code' => 0, 'data' => $transaction));
    }

    function getTransaction($idtrans,$transtype){
        $sql = "SELECT * FROM `transactions` JOIN `$transtype` ON `transactions`.`idtrans` = `$transtype`.`idtrans` WHERE `transactions`.`idtrans` = '$idtrans' AND `transactions`.`transtype` = '$transtype'";
        $transaction = executeResult($sql, false);
        if (!$transaction){
            die(json_encode(array('code' => 1, 'data' => 'Kh??ng c?? giao d???ch')));
        }
        echo json_encode(array('code' => 0, 'data' => $transaction));
    }
?>