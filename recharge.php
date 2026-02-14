<?php

//include html header
require_once("includes/html-header.php");

?>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">


        <?php

        //include sidebar
        require_once("includes/sidebar.php");

        ?>

        <!--  Main wrapper -->
        <div class="body-wrapper">


            <?php

            //include header
            require_once("includes/header.php");

            ?>



            <div class="container-fluid">
            <!-- notice -->
            <marquee class="alert alert-dark text-white" onmouseover="this.stop()" onmouseout="this.start()" role="alert" style="font-size: 1rem;">
                <?php $se = "SELECT * FROM notice";
                $data = mysqli_query($link, $se);
                $d = mysqli_fetch_assoc($data);
                echo $d['notice']; ?>
            </marquee>
            <!-- notice end -->
            

                <div class="card">
                    
                    <div class="card-body">
                       <h5 class="card-title fw-semibold mb-4 text-center">রিচার্জ করার জন্য অপেক্ষা করার দিন শেষ। প্রযুক্তিসেবায় মাত্র এক ক্লিকে এখনই রিচার্জ করে ফেলুন</h5>
                    

<div class="card">
    <div class="card-body">
        <form>
            <?php
            if (isset($_SESSION['Error'])) {
            ?>
                <div class="alert alert-danger" role="alert">
                    <?php 
                    echo $_SESSION['Error'];
                    $_SESSION['Error'] = null;
                    ?>
                </div>
            <?php
            } elseif (isset($_SESSION['Success'])) {
            ?>
                <div class="alert alert-success" role="alert">
                    <?php 
                    echo $_SESSION['Success'];
                    $_SESSION['Success'] = null;
                    ?>
                </div>
            <?php
            }
            ?>
            <div class="mb-3 text-center">
                <label for="exampleInputPassword1" class="form-label">টাকার পরিমান লিখুন।</label>
                <input type="text" class="form-control" id="amount">
            </div>
            <div class="mb-3 text-center">
                <button type="button" id="payNow" class="btn btn-outline-primary m-1">পে করুন।</button>
            </div>

            <div class="alert alert-danger" style="display: none;" id="amountErr" role="alert">
                A simple danger alert—check it out!
            </div>
        </form>
    </div>
</div>

                        

                    </div>
                </div>
                <div class="card w-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-semibold mb-4">পূর্ববর্তী লেনদেন</h5>
                        <div class="table-responsive">
                        <table class="table text-nowrap mb-0 align-middle">
                            <thead class="text-dark fs-4">
                            <tr>
                                <th class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">আইডি</h6>
                                </th>
                                <th class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">নাম্বার ও লেনদেন আইডি</h6>
                                </th>
                                <th class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">সময়</h6>
                                </th>
                                
                                <th class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">পরিমাণ</h6>
                                </th>
                                <th class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">স্ট্যাটাস</h6>
                                </th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php $sql = "SELECT * from payment where email = (:email) ORDER BY id DESC;";
							$query = $dbh->prepare($sql);
							$query->bindParam(':email', $_SESSION['alogin']);

							$query->execute();
							$results = $query->fetchAll(PDO::FETCH_OBJ);
							$cnt = 1;
							if ($query->rowCount() > 0) {
								foreach ($results as $result) {				
                            ?>
                                    <tr>
                                        <td class="border-bottom-0"><h6 class="fw-semibold mb-0"><?php echo htmlentities($result->id); ?></h6></td>
                                        <td class="border-bottom-0">
                                            <h6 class="fw-semibold mb-1">0<?php echo htmlentities($result->customerMsisdn); ?></h6>
                                            <span class="fw-normal"><?php echo htmlentities($result->trxID); ?></span>                          
                                        </td>
                                        <td class="border-bottom-0">
                                            <p class="mb-0 fw-normal"><?php echo htmlentities($result->paymentExecuteTime); ?></p>
                                        </td>
                                        
                                        <td class="border-bottom-0">
                                            <h6 class="fw-semibold mb-0 fs-4"><?php echo htmlentities($result->amount); ?> টাকা</h6>
                                        </td>
                                        <td class="border-bottom-0">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-success rounded-3 fw-semibold">সফল</span>
                                            </div>
                                        </td>
                                    </tr>
							<?php
								}
							} ?>

                             
                                                
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sidebarmenu.js"></script>
    <script src="assets/js/app.min.js"></script>
    <script src="assets/libs/simplebar/dist/simplebar.js"></script>


    <script>
    $(document).ready(function() {
      //generate token
      $.ajax({
        url: "lib/bkash/grand-token.php",
        type: "GET",
        contentType: "application/json",
        success: function(result) {
         // console.log(result);
        },
      });
      //payment option
      var btn = $("#payNow");
      //alert tag get
      var amountErr = $("#amountErr");
      btn.click(function() {
        //get the value of input fild
        var amount = $("#amount").val();
        
        
        
        //init var
        var amountValid;

        //validate amount
        if (amount.length == 0) {
          
          amountErr.text("টাকার পরিমান লিখুন।");
          amountErr.css({
            "display": "block"
          });
          amountValid = 0;
        } else if (isNaN(amount)) {
          amountErr.text("টাকার পরিমাণ সঠিক নয়।");
          amountErr.css({
            "display": "block"
          });
          amountValid = 0
        } else if (amount <= 01) {
          amountErr.text("সর্বন িম00 টাকা রিচার্জ করুন।");
          amountErr.css({
            "display": "block"
          });
          amountValid = 0
        } else {
          amountErr.css({
            "display": "none"
          });
          amountValid = 1;
        }



        //hit for payment
        if (amountValid == 1) {
          //change for loading
          btn.text('');
          btn.append("<img src=\"https://media.tenor.com/wpSo-8CrXqUAAAAj/loading-loading-forever.gif\" style=\"width: 20px;margin-right: 5px\"> গেটওয়ে লোড হচ্ছে।");
          btn.addClass('disabled');


          //getway
          $.ajax({

            url: "lib/bkash/create-payment.php",
            type: "POST",
            data: {
              amount: amount
            },
            // contentType: "application/json",
            success: function(response) {
              // console.log(response);
              location.href = response;

            },
          });


          //if not redirect
          setInterval(() => {
            btn.text('পরিশোধ করুন');
            btn.removeClass('disabled');
          }, 30000);
        }

      });
    });
    </script>
 <script>!function(){var e,t,n,a;window.MyAliceWebChat||((t=document.createElement("div")).id="myAliceWebChat",(n=document.createElement("script")).type="text/javascript",n.async=!0,n.src="https://widget.myalice.ai/index.js",(a=(e=document.body.getElementsByTagName("script"))[e.length-1]).parentNode.insertBefore(n,a),a.parentNode.insertBefore(t,a),n.addEventListener("load",(function(){MyAliceWebChat.init({selector:"myAliceWebChat",number:"Sakib76255",message:"",color:"#2AABEE",channel:"tg",boxShadow:"none",text:"Message Us",theme:"light",position:"right",mb:"20px",mx:"20px",radius:"20px"})})))}();</script>
</body>

</html>