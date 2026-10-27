<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function url(string $path=''): string { return rtrim(BASE_PATH, '/') . '/' . ltrim($path, '/'); }
function media_url(?string $path): string {
    $path=trim((string)$path);
    if($path==='') return url('assets/img/hero-clean2.jpg');
    if(preg_match('#^https?://#i',$path)) return $path;
    $path=ltrim($path,'/');
    if(str_starts_with($path,'assets/')) return url($path);
    if(str_starts_with($path,'uploads/')) return url('assets/'.$path);
    return url('assets/img/'.$path);
}
function redirect(string $path): never { header('Location: ' . url($path)); exit; }
function flash(string $type, string $message): void { $_SESSION['flash'][] = [$type, $message]; }
function flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }
function verify_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) die('Invalid form token.'); }
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $u;
    if ($u) return $u;
    $id = (int)$_SESSION['user_id'];
    $st = db()->prepare('SELECT * FROM users WHERE id=? LIMIT 1'); $st->bind_param('i',$id); $st->execute(); $u=$st->get_result()->fetch_assoc();
    return $u ?: null;
}
function login_user(array $u): void { session_regenerate_id(true); $_SESSION['user_id']=(int)$u['id']; }
function logout_user(): void { $_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'','time()-42000',$p['path'],$p['domain'],$p['secure'],$p['httponly']); } session_destroy(); }
function require_role(array $roles): array { $u=current_user(); if(!$u) redirect('login.php'); if(($u['status']??'suspended')!=='active'||!in_array($u['role'],$roles,true)) { logout_user(); redirect('index.php'); } return $u; }
function money(float $n): string { return CURRENCY.' '.number_format($n,2); }
function slugify(string $s): string { $s=strtolower(trim($s)); $s=preg_replace('/[^a-z0-9]+/','-',$s) ?? ''; return trim($s,'-'); }
function query(string $sql, string $types='', array $params=[]): mysqli_result|bool { $st=db()->prepare($sql); if($types){$refs=[];foreach($params as $k=>$_){$refs[$k]=&$params[$k];}array_unshift($refs,$types);$st->bind_param(...$refs);} $st->execute(); return $st->get_result(); }

function safe_log(string $action,string $entity,?int $entity_id=null,?array $meta=null): void {
    $uid=current_user()['id'] ?? null; $json=$meta?json_encode($meta):null;
    $st=db()->prepare('INSERT INTO audit_logs(user_id,action,entity,entity_id,meta,created_at) VALUES(?,?,?,?,?,NOW())');
    $st->bind_param('isiss',$uid,$action,$entity,$entity_id,$json); $st->execute();
}
function setting(string $key, string $default=''): string { $st=db()->prepare('SELECT value FROM settings WHERE key_name=? LIMIT 1'); $st->bind_param('s',$key); $st->execute(); $r=$st->get_result()->fetch_assoc(); return $r['value'] ?? $default; }
