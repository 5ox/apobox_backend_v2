require 'config.pl';
require 'common.pl';

testRebill();

#############################################################################
# Test a rebill
#
# This is a test method that calls the `rebill` method.
#############################################################################
sub testRebill {

  # Setup test input
  my $order_id = '1150116120571';

  # Make test request to rebill
  my ($result, $order_id, $response) = rebill(
    $order_id
  );

  print $result . "\n";
  print $order_id . "\n";
  print $response . "\n";
}

#############################################################################
# Charge an existing order
#############################################################################
sub rebill {
  use REST::Client;
  use JSON;

  # Inputs
  my $order_id = shift;

  print "\nRebilling order # " . $order_id . "...\n";

  my $url = '/api/orders/' . $order_id . '/charge',

  my $json_body = '{
    "data": {
      "type": "orders",
      "attributes": {
        "submit": "charge"
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

  my $response = from_json($client->responseContent());
  my $order = $response->{data}->{relationships};
  my $total = $order->{OrderTotal}->{data}->{value};

  print "Success \$" . $total . "\n";
  return ('success', $total, $response);
}
