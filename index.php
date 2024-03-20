<?php

use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if ($data === null) {
        http_response_code(400);
        return json_encode(['error' => 'Invalid JSON']);
    }

    $host = 'postgres3krut';
    $port = '5432';
    $dbname = 'postgres';
    $user = 'postgres';
    $password = 'postgres1';
    $conn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";

    if (!$conn) {
        return "Koneksi gagal: " . pg_last_error();
    } else {
        try {
            $dbh = new PDO($conn);
            $query = "SELECT * FROM queue_email";
            $result = $dbh->query($query);
            if ($result) {
                // Konfigurasi SMTP
                $mail = new PHPMailer();
                $mail->isSMTP();
                $mail->SMTPDebug = 2;
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 587;
                $mail->SMTPSecure = 'tls';
                $mail->SMTPAuth = true;
                $mail->AuthType = 'XOAUTH2';
                $email = 'forphpmailer2024@gmail.com';
                $clientId = '';
                $clientSecret = '';
                $refreshToken = '';

                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $provider = new Google(
                        [
                            'clientId' => $clientId,
                            'clientSecret' => $clientSecret,
                        ]
                    );

                    $mail->setOAuth(
                        new OAuth(
                            [
                                'provider' => $provider,
                                'clientId' => $clientId,
                                'clientSecret' => $clientSecret,
                                'refreshToken' => $refreshToken,
                                'userName' => $email,
                            ]
                        )
                    );

                    $mail->setFrom('hantujeruk1015@gmail.com', 'nfl');
                    $mail->addAddress($row['recipient_email']);
                    $mail->Subject = $row['subject'];
                    $mail->Body = $row['message'];

                    if (!$mail->send()) {
                        return "Mailer Error: " . $mail->ErrorInfo;
                    } else {
                        $updateStmt = $dbh->prepare("UPDATE queue_email SET sent = 1 WHERE id = :id");
                        $updateStmt->bindParam(':id', $row['id']);
                        $updateStmt->execute();
                        return 'Email berhasil terkirim ke: ' . $row['recipient'] . '<br>';
                    }
                }
            } else {
                return "Gagal menjalankan query SELECT.";
            }
        } catch (PDOException $e) {
            return "Koneksi gagal: " . $e->getMessage();
        }
    }
    
} else {
    http_response_code(405);
    return json_encode(['error' => 'Method Not Allowed']);
}