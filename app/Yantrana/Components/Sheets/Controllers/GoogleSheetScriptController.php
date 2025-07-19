<?php
// app/Http/Controllers/GoogleSheetScriptController.php

namespace App\Yantrana\Components\Sheets\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Yantrana\Components\WhatsAppService\Repositories\WhatsAppTemplateRepository;

class GoogleSheetScriptController extends Controller
{
    protected $whatsAppTemplateRepository;

    public function __construct(WhatsAppTemplateRepository $whatsAppTemplateRepository)
    {
        $this->whatsAppTemplateRepository = $whatsAppTemplateRepository;
    }

    public function index()
    {
        $whatsAppTemplates = $this->whatsAppTemplateRepository->getApprovedTemplatesByNewest();
        return view('google-sheet-script.index', compact('whatsAppTemplates'));
    }

    public function generateScript(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_name' => 'required|string',
            'phone_column_index' => 'required|integer|min:0',
            'custom_fields' => 'array|max:5',
            'custom_fields.*' => 'integer|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $templateName = $request->input('template_name');
        $phoneColumnIndex = $request->input('phone_column_index');
        $customFields = $request->input('custom_fields', []);

        // Fetch the template to get its language
        $template = $this->whatsAppTemplateRepository->fetchIt([
            'template_name' => $templateName
        ]);

        if (!$template) {
            return redirect()->back()
                ->withErrors(['template_name' => 'Template not found'])
                ->withInput();
        }

        // Get the template language from the template
        $templateLanguage = $template->language;

        // Get vendor settings using the same helper functions as api-access.blade.php
        $apiToken = getVendorSettings('vendor_api_access_token');
        $apiUrl = route('api.base_url');
        $vendorUid = getVendorUid();
        
        // Construct the API endpoint URL the same way as in api-access.blade.php
        $fullApiUrl = route('api.vendor.chat_message.send.process', [
            'vendorUid' => $vendorUid
        ]);

        // Generate the script
        $script = $this->generateAppScript(
            $fullApiUrl,
            $apiToken,
            $vendorUid,
            $templateName,
            $templateLanguage,
            $phoneColumnIndex,
            $customFields
        );

        return view('google-sheet-script.result', [
            'script' => $script,
            'templateName' => $templateName,
            'templateLanguage' => $templateLanguage,
            'phoneColumnIndex' => $phoneColumnIndex,
            'customFields' => $customFields
        ]);
    }

    private function getVendorSettings($key)
    {
        // Use the same helper function as in api-access.blade.php
        return getVendorSettings($key);
    }

    private function getVendorUid()
    {
        // Use the same helper function as in api-access.blade.php
        return getVendorUid();
    }

    private function generateAppScript($apiUrl, $apiToken, $vendorUid, $templateName, $templateLanguage, $phoneColumnIndex, $customFields)
    {
        // Get base API URL and construct the endpoint
        $baseApiUrl = route('api.base_url');
        
        // Using nowdoc syntax (single quotes) to prevent variable interpolation
        $script = <<<'EOT'
/**
 * Google Sheets script to send webhook on new row additions
 * 
 * This script monitors a Google Sheet for new rows and sends API requests
 * to an external endpoint with phone number data from each new row.
 */

// URL for the API endpoint
EOT;
        
        // Append dynamic values using concatenation
        $script .= "\nconst API_URL = \"" . $baseApiUrl . "/" . $vendorUid . "/contact/send-template-message\";\n";
        $script .= "const API_TOKEN = \"" . $apiToken . "\";\n\n";
        
        $script .= <<<'EOT'
// We'll store the last processed row count to track new additions
const PROPERTY_NAME = "lastProcessedRowCount";

/**
 * Creates a trigger to run the checkForNewRows function every minute
 */
function createTrigger() {
  // Delete any existing triggers
  const triggers = ScriptApp.getProjectTriggers();
  for (let i = 0; i < triggers.length; i++) {
    ScriptApp.deleteTrigger(triggers[i]);
  }
  
  // Create a new trigger that runs every minute
  ScriptApp.newTrigger("checkForNewRows")
    .timeBased()
    .everyMinutes(1)
    .create();
    
  Logger.log("Trigger created successfully");
}

/**
 * Checks for new rows and processes them
 */
function checkForNewRows() {
  try {
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    const dataRange = sheet.getDataRange();
    const values = dataRange.getValues();
    
    // Get the current row count (excluding header row)
    const currentRowCount = values.length - 1;
    
    // Get the last processed row count from properties
    const scriptProperties = PropertiesService.getScriptProperties();
    let lastProcessedRowCount = parseInt(scriptProperties.getProperty(PROPERTY_NAME) || "0");
    
    // If there are new rows
    if (currentRowCount > lastProcessedRowCount) {
      // Process each new row
      for (let i = lastProcessedRowCount + 1; i <= currentRowCount; i++) {
        const rowData = values[i];
EOT;
        
        $script .= "\n        // Get phone number from column " . ($phoneColumnIndex + 1) . " (0-based index " . $phoneColumnIndex . ")\n";
        $script .= "        const phoneNumber = rowData[" . $phoneColumnIndex . "];\n";
        
        $script .= <<<'EOT'
        
        // Send the API request with the row data
        sendApiRequest(phoneNumber, rowData);
      }
      
      // Update the last processed row count
      scriptProperties.setProperty(PROPERTY_NAME, currentRowCount.toString());
    }
  } catch (error) {
    Logger.log("Error: " + error.toString());
  }
}

/**
 * Sends the API request with the phone number
 * @param {string} phoneNumber - The phone number to send the message to
 * @param {Array} rowData - The entire row data
 */
function sendApiRequest(phoneNumber, rowData) {
  try {
    // Format the phone number - remove any non-digit characters if needed
    const formattedPhoneNumber = phoneNumber.toString().replace(/\D/g, "");
    
    // Create the payload
    const payload = {
      "phone_number": formattedPhoneNumber,
EOT;
    
        $script .= "\n      \"template_name\": \"" . $templateName . "\",";
        $script .= "\n      \"template_language\": \"" . $templateLanguage . "\"";
    
        // Add custom fields to payload
        foreach ($customFields as $index => $columnIndex) {
            $fieldNumber = $index + 1;
            $script .= ",\n      \"field_{$fieldNumber}\": rowData[{$columnIndex}]";
        }

        $script .= <<<'EOT'
    };
    
    // Set up the request options
    const options = {
      "method": "post",
      "contentType": "application/json",
      "payload": JSON.stringify(payload),
      "muteHttpExceptions": true
    };
    
    // Make the API request
    const fullUrl = `${API_URL}?token=${API_TOKEN}`;
    const response = UrlFetchApp.fetch(fullUrl, options);
    
    // Log the response
    Logger.log("API Response: " + response.getContentText());
    
    // Add a note to the cell indicating the message was sent
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
EOT;

        // Add code to find the row based on a unique identifier in the first column if possible
        $script .= "\n    const rowIndex = rowData[0] ? sheet.createTextFinder(rowData[0]).findNext().getRow() : sheet.getLastRow();\n";
        $script .= "    sheet.getRange(rowIndex, " . ($phoneColumnIndex + 1) . ").setNote(\"Message sent: \" + new Date().toLocaleString());\n";
    
        $script .= <<<'EOT'
    
  } catch (error) {
    Logger.log("API Request Error: " + error.toString());
  }
}

/**
 * Initialize the script on spreadsheet open
 */
function onOpen() {
  const ui = SpreadsheetApp.getUi();
  ui.createMenu('API Integration')
    .addItem('Start Monitoring', 'createTrigger')
    .addItem('Process Now', 'checkForNewRows')
    .addToUi();
}
EOT;

        return $script;
    }
}