<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parts extends CI_Controller {

    public function search()
    {
        header("Content-Type: application/json");

        // Get POST JSON
        $input = json_decode(file_get_contents("php://input"), true);

        $search = "";
        if (!empty($input['partname'])) {
            $search = strtolower(trim($input['partname']));
        }

        // 🔥 Gemini AI (mechanic style)
        if (!empty($search)) {
            $search = $this->geminiProcess($search);
        }

        // Load JSON data
        $json = file_get_contents(APPPATH . 'data/parts.json');
        $data = json_decode($json, true);

        if (!is_array($data)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON data"
            ]);
            return;
        }

        $result = [];

        // Keywords
        $keywords = array_filter(explode(" ", $search));
        $keywords = array_slice($keywords, 0, 2);

        foreach ($data as $item) {

            if (!isset($item['partTypeName'])) continue;

            $name = strtolower(trim($item['partTypeName']));
            $desc = isset($item['description']) ? strtolower(trim($item['description'])) : "";

            $searchText = $name . " " . $desc;

            $score = 0;

            // 🔥 Exact phrase match (highest priority)
            if (!empty($search) && stripos($searchText, $search) !== false) {
                $score += 5;
            }

            // 🔹 Keyword scoring
            foreach ($keywords as $word) {
                if (stripos($name, $word) !== false) $score += 2;
                if (stripos($desc, $word) !== false) $score += 1;
            }

            // 🔥 Fix: Single-word vs multi-word handling
            if (count($keywords) == 1) {
                if ($score >= 2) {
                    $item['score'] = $score;
                    $result[] = $item;
                }
            } else {
                if ($score >= 3) {
                    $item['score'] = $score;
                    $result[] = $item;
                }
            }
        }

        // 🔥 Sort by score
        usort($result, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // 🔥 Limit results
        $result = array_slice($result, 0, 10);

        // Remove score
        foreach ($result as &$r) {
            unset($r['score']);
        }

        // 🔥 Response
        if (count($result) > 0) {
            echo json_encode([
                "status" => "success",
                "search_used" => $search,
                "count" => count($result),
                "data" => $result
            ]);
        } else {
            echo json_encode([
                "status" => "success",
                "message" => "No data found",
                "search_used" => $search,
                "count" => 0,
                "data" => []
            ]);
        }
    }

    // 🔥 Gemini 2.5 Flash (Mechanic Prompt)
    private function geminiProcess($search)
    {
        $apiKey = "YOUR_GEMINI_API_KEY";

        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

        $postData = [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => "You are an experienced automobile mechanic. 
                                When a customer describes a part in simple words, identify the exact vehicle part name. 
                                Return only 1 or 2 correct part keywords (like 'fuel pump', 'power plug', '4wd actuator'). 
                                Do not explain anything. Input: " . $search                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return $search;
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $search;
        }

        $aiText = $result['candidates'][0]['content']['parts'][0]['text'];

        // Clean output
        $aiText = preg_replace('/[^a-zA-Z0-9 ]/', '', $aiText);
        $aiText = strtolower(trim($aiText));

        if (strlen($aiText) < 2) {
            return $search;
        }

        return $aiText;
    }
}