<?php

class Hash {

    // ハッシュ値を作る
    public function generatePasswordHash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    // ハッシュ値を照合する
    public function verifyPasswordHash($password1, $password2) {
        return password_verify($password1, $password2);
    }
}