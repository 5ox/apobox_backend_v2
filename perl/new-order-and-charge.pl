require 'config.pl';
require 'common.pl';

testOrderAndCharge();

#############################################################################
# Test the new order and charge API endpoints
#
# This is a test method that calls the `newOrder` and `chargeOrder` methods.
# It provides a good example of what code you will need to implement along with
# the additional methods in the remainder of this file.
#############################################################################
sub testOrderAndCharge {

  # Setup test input
  my $billing_id = 'JT1471';
  my $billing_address_id = undef;
  my $shipping_address_id = undef;
  my $handling = 10;
  my $insurance = 5;
  my $uspsfee = 6;
  my $repackfee = 2;
  my $reshipfee = 2;
  my $noapoidfee = 3; # Not currently supported

  # Make test request to newOrder
  my ($result, $order_id, $response) = newOrder(
    $billing_id,
    $billing_address_id,
    $shipping_address_id
  );

  if ($result eq 'failure') {
    print "Order creation failed\n";
    print $result . "\n";
    print $order_id . "\n";
    print $response . "\n";
	return false;
  }

  # Make test request to chargeOrder
  my ($result, $order_id, $response) = chargeOrder(
    $order_id,
    $handling,        # $HANDLING = shift;
    $uspsfee,         # $USPSFEE = shift;
    $insurance,       # $INSURANCE = shift;
    $repackfee,       # $REPACKFEE = shift;
    $reshipfee,       # $RESHIPFEE = shift;
    $noapoidfee,      # $NOAPOIDFEE = shift;
  );

  print $result . "\n";
  print $order_id . "\n";
  print $response . "\n";
}

#############################################################################
# Create a new order
#############################################################################
sub newOrder {
  use REST::Client;
  use JSON;

  # Inputs
  my $billing_id = shift;
  my $billing_address_id = shift;
  my $shipping_address_id = shift;

  print "\nSubmitting new order for " . $billing_id . "...\n";

  my $url = '/orders/' . $billing_id . '/add';

  my $json_body = '{
    "data": {
      "type": "orders",
      "attributes": {
        "carrier": "usps",
        "inbound_tracking_number": "No inbound tracking number",
        "insurance_coverage": "200.00",
        "width": "12",
        "length": "12",
        "depth": "12",
        "weight_oz": "10",
        "mail_class": "priority",
        "package_type": "rectparcel",
        "customers_address_id": "' . $billing_address_id . '",
        "delivery_address_id": "' . $shipping_address_id . '",
        "billing_address_id": "' . $billing_address_id . '",
        "comments": "This is a test"
      }
    }
  }';

  my $client = REST::Client->new();
  $client->setHost($rest_api);
  print Dumper($json_body);
  $client->POST(
	$url,
    $json_body,
    apiHeaders()
  );

  if (BadResponseCode($client->responseCode())) {
	print "Failure\n";
    return ProcessBadResponse($client->responseCode(), $client->responseContent());
  }

  my $response = from_json($client->responseContent());
  my $order = $response->{data}->{attributes};
  my $order_id = $order->{orders_id};

  print "Success Order # " . $order_id . "\n";
  return ('success', $order_id, $response);
}

#############################################################################
# Charge an existing order
#############################################################################
sub chargeOrder {
  use REST::Client;
  use JSON;

  # Inputs
  my $order_id = shift;
  my $HANDLING = shift;
  my $USPSFEE = shift;
  my $INSURANCE = shift;
  my $REPACKFEE = shift;
  my $RESHIPFEE = shift;
  my $NOAPOIDFEE = shift;

  $HANDLING = sprintf "%.2f", $HANDLING;
  $USPSFEE = sprintf "%.2f", $USPSFEE;
  $INSURANCE = sprintf "%.2f", $INSURANCE;
  $REPACKFEE = sprintf "%.2f", $REPACKFEE;
  $RESHIPFEE = sprintf "%.2f", $RESHIPFEE;
  $NOAPOIDFEE = sprintf "%.2f", $NOAPOIDFEE;

  print "\nCharging order # " . $order_id . "...\n";

  my $url = '/orders/' . $order_id . '/charge',

  my $items_json = '
    "OrderStorage": {
      "data": { "value": "0.00" }
    },
  ';

  # These won't be available until we push another update and even so, only
  # two custom fields will be available. OrderStorage above could be used
  # for one of them.

  if ($REPACKFEE > 1) {
    $items_json .= '
      "OrderCustom1": {
        "data": {
          "name": "Package Repacking",
          "value": "' . $REPACKFEE . '"
        }
      },
    ';
  }
  if ($RESHIPFEE > 1) {
    $items_json .= '
      "OrderCustom2": {
        "data": {
          "name": "Package Reshipping",
          "value": "' . $RESHIPFEE . '"
        }
      },
    ';
  }

  # Currently only support 2 custom fields
  #if ($NOAPOIDFEE > 1) {
  #  $items_json .= '
  #    "OrderCustom1": {
  #      "data": {
  #        "name": "Address Correction",
  #        "value": "' . $NOAPOIDFEE . '"
  #      }
  #    },
  #  ';
  #}

  my $json_body = '{
    "data": {
      "type": "orders",
      "attributes": {
        "submit": "charge",
        "orders_status": "3"
      },
      "relationships": {
        ' . $items_json . '
        "OrderShipping": {
          "data": { "value": "' . $USPSFEE . '" }
        },
        "OrderInsurance": {
          "data": { "value": "' . $INSURANCE . '" }
        },
        "OrderFee": {
          "data": { "value": "' . $HANDLING . '" }
        }
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
