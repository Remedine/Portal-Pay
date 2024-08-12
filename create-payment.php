<?php

namespace OverlordNews;

function send_payment_request()
{
    $url = 'https://api.portal-pay.xyz/payments';

    $data_encryption = new Data_Encryption();
    $encrypted_api_key = get_option('PortalPay_api_key');

    if ($encrypted_api_key) {
        $api_key = $data_encryption->decrypt($encrypted_api_key);
    }

    $headers = array(
        'Accept'        => 'application/json',
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type'  => 'application/json',
    );

    $body = json_encode(array(
        'currency_id' => 1016,
        'amount' => 10,
        'description' => 'Overlord News',
        'customer_identifier' => '80085135',
        'order_identifier' => '80085',
        'line_items' => array(
            array(
                'Title' => 'Monthly Subscription',
                'Quantity' => 1,
                'Price' => 9,
                'Image' => 'http://portal-pay-dev.local/wp-content/uploads/2024/08/kelly-sikkema-fjNaOMcYNUs-unsplash-scaled.jpg',
            ),
            array(
                'Title' => 'Overlord News Sticker',
                'Quantity' => 1,
                'Price' => 1,
                'Image' => 'http://portal-pay-dev.local/wp-content/uploads/2024/08/kelly-sikkema-fjNaOMcYNUs-unsplash-scaled.jpg',
            ),
        ),
        'metadata' => 'Comment: Please go deep into conspiracies.',
        'redirect_meta' => array(
            'success_url' => 'http://portal-pay-dev.local/success',
            'cancel_url' => 'http://portal-pay-dev.local/cancel',
            'failure_url' => 'http://portal-pay-dev.local/failure',
        ),
    ));

    $args = array(
        'body'    => $body,
        'headers' => $headers,
        'method'  => 'POST',
    );

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
        error_log("Payment Request Error: $error_message");
    } else {
        $response_body = wp_remote_retrieve_body($response);
        $decoded_response = json_decode($response_body, true);

        // Output response in the frontend (if needed)
        echo 'Response:<pre>';
        print_r($decoded_response);
        echo '</pre>';

        // Log response to debug log
        error_log('Payment Request Response: ' . print_r($decoded_response, true));

        // Check if payment_url exists in the response and output JavaScript to open it in a new tab
        if (isset($decoded_response['payment_url'])) {
            $payment_url = esc_url_raw($decoded_response['payment_url']);

            // Use wp_footer to output JavaScript that opens the URL in a new tab
            add_action('wp_footer', function () use ($payment_url) {
                echo "<script type='text/javascript'>
                    window.open('" . $payment_url . "', '_blank');
                </script>";
            }, 100);
        } else {
            echo 'Payment URL not found in the response.';
        }
    }
}

// Hook this function to an action or directly call it as needed
add_action('wp_footer', 'OverlordNews\\send_payment_request');
