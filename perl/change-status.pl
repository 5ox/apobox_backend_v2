require 'config.pl';
require 'common.pl';

testChangeStatus();

#############################################################################
# Test the changestatus API endpoints
#
# This is a test method that calls the `changeStatus` method.
# It provides a good example of what code you will need to implement along with
# the additional methods in the remainder of this file.
#############################################################################
sub testChangeStatus {
  # Setup test input
  my $order_id = '1150116120572';
  my $new_status = 3;
  my $outbound_tracking_number = '1234567890';
  my $notify_customer = 1;
  my $comment = 'This is a test';

  # Make test request to newOrder
  my ($result, $order_id, $response) = changeStatus(
    $order_id,
    $new_status,
    $outbound_tracking_number,
    $notify_customer,
    $comment
  );

  print $result . "\n";
  print $order_id . "\n";
  print $response . "\n";
}

#############################################################################
# Update an order status
#############################################################################
sub changeStatus {
  use REST::Client;
  use JSON;

  # Inputs
  my $order_id = shift;
  my $new_status = shift;
  my $outbound_tracking_number = shift || '';
  my $notify_customer = shift || 0;
  my $comment = shift || '';

  print "\nChanging status for order " . $order_id . "...\n";

  my $url = '/orders/changestatus';

  my $json_body = '{
    "data": {
      "type": "orders",
      "id": "' . $order_id . '",
      "attributes": {
        "orders_status": "' . $new_status . '",
        "status_history_comments": "' . $comment . '",
        "usps_track_num": "' . $outbound_tracking_number . '",
        "notify_customer":  "' . $notify_customer . '"
      }
    }
  }';

  my $client = REST::Client->new();
  $client->setHost($rest_api);
  print Dumper($json_body);
  $client->PATCH(
	$url,
    $json_body,
    apiHeaders()
  );

  if (BadResponseCode($client->responseCode())) {
	print "Failure\n";
    return ProcessBadResponse($client->responseCode(), $client->responseContent());
  }

  print "Success Order # " . $order_id . "\n";
  return ('success', $order_id, '');
}
