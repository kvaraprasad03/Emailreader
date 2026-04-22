<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** @property CI_Ticket_model $Ticket_model
 *  @property CI_config $config
 */

class EmailReader extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Ticket_model');
        $this->load->config('config');
        date_default_timezone_set('Asia/Kolkata');
    }

    public function fetch_emails() {

        header('Content-Type: application/json');

        $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
        $username = 'prasadzero03@gmail.com';
        $password = 'yzvj ozsw nedm uytj';

        $inbox = imap_open($hostname, $username, $password) or die(imap_last_error());

        $emails = imap_search($inbox, 'UNSEEN');

        $output = [];

        if ($emails) 
        {
            rsort($emails);

            $limit = 10;
            $emails = array_slice($emails, 0, $limit);

            foreach ($emails as $email_number) {

                $overview = imap_fetch_overview($inbox, $email_number, 0);

                $subject = $overview[0]->subject ?? '';
                $from = $overview[0]->from ?? '';
                $raw_date = $overview[0]->date ?? '';
                $uid = $overview[0]->message_id ?? '';

                $timestamp = strtotime($raw_date);
                $date = $timestamp ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');

                $message = $this->get_email_body($inbox, $email_number);
                $message = quoted_printable_decode($message);
                $message = $this->clean_email_body($message);

                $summary = $this->summarize_text($message);

                preg_match('/<(.+?)>/', $from, $email_match);
                $email = $email_match[1] ?? $from;
                $name = trim(explode('<', $from)[0]);

                $data = [
                    'subject' => $subject,
                    'sender_email' => trim($email),
                    'customer_name' => $name,
                    'description' => $summary,
                    'created_at' => $date,
                    'email_uid' => $uid
                ];

                if (!empty($summary) && $summary != "No issue found") {
                    $this->Ticket_model->insert_ticket($data);
                }

                // MARK AS READ
                imap_setflag_full($inbox, $email_number, "\\Seen");

                $output[] = [
                    'original_body' => $message,
                    'summary' => $summary
                ];
            }
        }

        imap_close($inbox);

        echo json_encode([
            'status' => 'success',
            'data' => $output
        ]);
    }

    private function get_email_body($inbox, $email_number) {

    $structure = imap_fetchstructure($inbox, $email_number);

    return $this->extract_part($inbox, $email_number, $structure);
}

private function extract_part($inbox, $email_number, $structure, $part_number = null) {

    $data = "";

    if ($structure->type == 0) { // TEXT

        $part_number = $part_number ?: 1;
        $data = imap_fetchbody($inbox, $email_number, $part_number);

        if ($structure->encoding == 3) {
            $data = base64_decode($data);
        } elseif ($structure->encoding == 4) {
            $data = quoted_printable_decode($data);
        }

        return $data;
    }

    // 🔁 MULTIPART → COLLECT ALL PARTS
    if ($structure->type == 1 && isset($structure->parts)) {

        $full_body = '';

        foreach ($structure->parts as $index => $sub_structure) {

            $prefix = $part_number ? $part_number . '.' : '';
            $part_no = $prefix . ($index + 1);

            $result = $this->extract_part($inbox, $email_number, $sub_structure, $part_no);

            if (!empty($result)) {
                $full_body .= " " . $result;
            }
        }

        return $full_body;
    }

    return "";
}
    private function clean_email_body($message) {

        if (empty($message)) return '';

        // Remove HTML
        $message = strip_tags($message);

        // Decode entities
        $message = html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // ✅ REMOVE DUPLICATE BLOCK (IMPORTANT)
        $words = explode(' ', $message);
        $half = intval(count($words) / 2);

        $first_half = implode(' ', array_slice($words, 0, $half));
        $second_half = implode(' ', array_slice($words, $half));

        similar_text($first_half, $second_half, $percent);

        if ($percent > 80) {
            $message = $first_half; // keep only one copy
        }

        // ✅ Clean spaces
        $message = preg_replace('/\s+/', ' ', $message);

        return trim($message);
    }

    private function summarize_text($text) {

    if (empty(trim($text))) return '';

    $api_key = $this->config->item('gemini_api_key');

    $prompt = "Read the following email carefully.

    Extract ALL issues mentioned in the email.

    STRICT RULES:
    - Return ONLY bullet points
    - Each issue must be on a new line starting with '-'
    - Do NOT stop early
    - Do NOT return partial sentences
    - Do NOT miss any issue
    - Ensure each bullet is a COMPLETE sentence ending with a period
    - Cover login, performance, navigation, and email issues if present
    - Each bullet must be a complete sentence ending with a period
    - Do NOT stop early
    - Ensure complete sentences
    - Continue until all issues are listed
    - If Telugu,Hindi or other languages is typed in English letters, convert it to proper English
    Example:

    Email:
    Login is not working. Dashboard is slow. Password reset not working.

    Output:
    - Login is not working.
    - Dashboard is slow.
    - Password reset is not working.

    Now process this email completely:

    " . $text;
    $payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $prompt]
            ]
        ]
    ],
    /*"generationConfig" => [
        "temperature" => 0.2,
        "maxOutputTokens" => 800
    ]*/
    ];

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $api_key;

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    curl_close($ch);

    //  CURL ERROR
    if ($curl_error) {
        return "Curl error: " . $curl_error;
    }

    $result = json_decode($response, true);

    //  API ERROR HANDLING (IMPORTANT)
    if ($http_code != 200 || isset($result['error'])) {

        if (isset($result['error']['status'])) {

            if ($result['error']['status'] == 'RESOURCE_EXHAUSTED') {
                return "API quota exceeded";
            }

            return $result['error']['message'];
        }

        return "API request failed";
    }
    //  SUCCESS RESPONSE
        $summary = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        $summary = trim($summary);

        // remove unwanted text
        $summary = preg_replace('/Here are.*?:/i', '', $summary);

        // normalize bullets
        $summary = str_replace(["•", "*"], "-", $summary);

        // fix formatting
        $summary = preg_replace('/\s*-\s*/', "\n- ", $summary);

        // clean spaces
        $summary = preg_replace('/[ \t]+/', ' ', $summary);

        $summary = trim($summary);

        if (trim($summary) == "-" || empty(trim($summary))) {
            return "No issues Found.";
        }

        return $summary;
}
}