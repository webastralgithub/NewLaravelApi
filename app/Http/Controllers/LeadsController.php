<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LeadsController extends Controller
{
    public function getRecentProspects()
    {
        $accessToken = env('ACCESS_TOKEN');

        $client = new Client();

        try {
            $response = $client->request('GET', 'https://sandbox.zohoapis.com.au/crm/v3/Prospects', [
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json'
                ],
                'query' => [
                    'sort_by' => 'Created_Time',
                    'sort_order' => 'desc',
                    'page' => 1,
                    'per_page' => 5,
                    'fields' => '*(*)' // Include all fields for the prospects
                ]
            ]);

            $prospects = json_decode($response->getBody(), true)['data'];
          $data = $this->getProspectusById($prospects);
            return response()->json($data);


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getProspectusById($prospects){

        $accessToken = env('ACCESS_TOKEN');
        $data=array();
        foreach ($prospects as $id){
            $client = new Client();
            $response = $client->request('GET', 'https://sandbox.zohoapis.com.au/crm/v3/Prospects/'.$id['id'], [
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json'
                ],
                'query' => [
                    'sort_by' => 'Created_Time',
                    'sort_order' => 'desc',
                    'page' => 1,
                    'per_page' => 5,
                ]
            ]);

            $data[] = json_decode($response->getBody(), true)['data'];

        }
        return $data;
    }
   //Create Prospectus
    public function createProspect(Request $request)
    {
        $accessToken = env('ACCESS_TOKEN');

        $client = new Client();

        try {
            $response = $client->request('POST', 'https://sandbox.zohoapis.com.au/crm/v3/Leads', [
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'data' => [
                        [
                            'First_Name' => $request->input('first_name'),
                            'Last_Name' => $request->input('last_name'),
                            'Mobile' => $request->input('mobile'),
                            'Email' => $request->input('email'),
                            'DOB' => $request->input('dob'),
                            'Tax_File_Number' => $request->input('tax_file_number'),
                            'Agreed_Terms' => $request->input('agreed_terms'),
                            'Status' => $request->input('status')
                            // Include other required and optional fields for the prospect
                        ]
                    ]
                ]
            ]);

            $prospect = json_decode($response->getBody(), true)['data'][0];
            // Send email with the response data
            $to = 'it@truewealth.com.au';
            $subject = 'New Prospectus';
            $message = 'A new prospectus has been created.' . PHP_EOL . PHP_EOL;
            $message .= 'Prospect Details:' . PHP_EOL;
            $message .= 'First Name: ' . $prospect['First_Name'] . PHP_EOL;
            $message .= 'Last Name: ' . $prospect['Last_Name'] . PHP_EOL;
            $message .= 'Mobile: ' . $prospect['Mobile'] . PHP_EOL;
            $message .= 'Email: ' . $prospect['Email'] . PHP_EOL;
            $message .= 'DOB: ' . $prospect['DOB'] . PHP_EOL;
            $message .= 'Tax File Number: ' . $prospect['Tax_File_Number'] . PHP_EOL;
            $message .= 'Agreed Terms: ' . $prospect['Agreed_Terms'] . PHP_EOL;
            $message .= 'Status: ' . $prospect['Status'] . PHP_EOL;
            // Include other relevant prospect details

            // Send email using the mail() function
            mail($to, $subject, $message);

            return response()->json($prospect, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
