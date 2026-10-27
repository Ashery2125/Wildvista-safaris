<?php
// One-time configuration helper: create the DB connection file and import schema/seed after the database exists.
if($_SERVER['REQUEST_METHOD']==='POST'){
 $host=trim($_POST['host']);$name=trim($_POST['name']);$user=trim($_POST['user']);$pass=(string)($_POST['pass']??'');
 $db=@new mysqli($host,$user,$pass,$name);if($db->connect_errno){$err='Connection failed: '.$db->connect_error;}else{
  $cfg="<?php\nconst DB_HOST=".var_export($host,true).";\nconst DB_NAME=".var_export($name,true).";\nconst DB_USER=".var_export($user,true).";\nconst DB_PASS=".var_export($pass,true).";\nconst SITE_NAME='Wildvista'; const SITE_TAGLINE='Explore Arusha Your Way'; const BASE_PATH='/'; const CURRENCY='USD'; const ADMIN_EMAIL='akilykaaya@gmail.com'; const ADMIN_PHONE='+255 794 872 433';\n";
  if(@file_put_contents(__DIR__.'/app/config/config.php',$cfg)===false){$err='Connected, but could not write app/config/config.php. Edit it manually.';}else{
   $sql=file_get_contents(__DIR__.'/database/schema_seed.sql');
   if(!$db->multi_query($sql)){$err='Config saved, but schema import failed: '.$db->error;}else{do{$db->store_result();}while($db->more_results()&&$db->next_result());$ok=true;}
  }
 }
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Wildvista setup</title><style>body{font-family:system-ui;background:#f5f1e8;color:#092f25;padding:30px}.box{max-width:620px;margin:auto;background:#fffdf8;padding:30px;border-radius:22px}label{display:block;margin:14px 0 6px;font-weight:700}input{width:100%;padding:12px;border:1px solid #ddd;border-radius:10px;box-sizing:border-box}button{margin-top:20px;padding:13px 18px;background:#06382c;color:#fff;border:0;border-radius:10px;font-weight:800}.ok{background:#e8f4ea;padding:12px}.err{background:#fee9e8;padding:12px}</style></head><body><div class="box"><h1>Wildvista Setup</h1><p>Create the MySQL database first in your hosting panel, then enter its exact credentials here.</p><?php if(isset($ok)): ?><div class="ok">Setup completed. Delete <strong>setup.php</strong> now, then open the site.</div><?php elseif(isset($err)): ?><div class="err"><?=htmlspecialchars($err)?></div><?php endif; ?><form method="post"><label>Database host</label><input name="host" value="localhost" required><label>Database name</label><input name="name" placeholder="your_account_wildvista" required><label>Database user</label><input name="user" required><label>Database password</label><input type="password" name="pass"><button>Configure & Import</button></form></div></body></html>
