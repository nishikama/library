<?php

class tokenClass
{
    /**
     * CSRFトークンの生成
     *
     * @return string トークン
     */
    public function generateToken()
    {
        // セッションIDからハッシュを生成
        return hash('sha256', session_id());
    }

    /**
     * CSRFトークンの検証
     *
     * @param string $token
     * @return bool 検証結果
     */
    public function validateToken($token)
    {
        // 送信されてきた$tokenがこちらで生成したハッシュと一致するか検証
        return $token === $this->generateToken();
    }
}
