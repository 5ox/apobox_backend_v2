# Requirements
#
# The changes require installation of:
#   Data::Dumper
#   REST::Client
#   JSON
#   Mozilla::CA  - for verifying ssl certificates
#

# Testing
#
# While testing in development, NOT IN PRODUCTION, and are getting "certificate
# verify failed" errors, you can uncomment this line to allow testing a server
# without a publically verifiable SSL certificate.
#
# $ENV{'PERL_LWP_SSL_VERIFY_HOSTNAME'} = 0;


# Global Configuration
our $rest_api = 'https://c3.propic.com:3000';
#our $rest_api = 'https://127.0.0.1:4433';
our $bearerToken = 'ASDF2E4V6ye2Fa9t5F3R21';


use Data::Dumper;

testChargeCC();

# Test method
sub testChargeCC {

  # Setup test input
  my $billing_id = 'ABC123';
  my $billing_address_id = undef;
  my $shipping_address_id = undef;
  my $handling = 10;
  my $insurance = 5;
  my $uspsfee = 6;
  my $repackfee = 2;
  my $reshipfee = 2;
  my $noapoidfee = 3;
  my $total = $handling + $insurance + $uspsfee + $repackfee + $reshipfee + $noapoidfee;

  # Make test request to ChargeCC
  my ($result, $paypal_id, $message) = ChargeCC(
    $billing_id,
    $billing_address_id,
    $shipping_address_id,
    $total,           # $TOTAL
    'not-used',       # $CCNUM
    'not-used',       # $CCEXPYEAR
    'not-used',       # $CCEXPMO
    'not-used',       # $CCFNAME
    'not-used',       # $CCLNAME
    'not-used',       # $COUNTRY = shift;
    'not-used',       # $STREET = shift; $STREET =~ s/[&]/ and /g; # FILTER $STREET =~ s/[^\w\s]//g;  # FILTER
    'not-used',       # $CITY = shift;
    'not-used',       # $STATE = shift;
    'not-used',       # $ZIP = shift;
    'not-used',       # $PHONE = shift;
    'not-used',       # $CCCVV = shift;
    'not-used',       # $SFNAME = shift; $SFNAME =~ s/[&]/ and /g; # FILTER $SFNAME =~ s/[^\w\s]//g;  # FILTER
    'not-used',       # $SLNAME = shift; $LFNAME =~ s/[&]/ and /g; # FILTER $LFNAME =~ s/[^\w\s]//g;  # FILTER
    'not-used',       # $COUNTRY = shift;
    'not-used',       # $SSTREET = shift; $SSTREET =~ s/[&]/ and /g; # FILTER $SSTREET =~ s/[^\w\s]//g;  # FILTER
    'not-used',       # $SCITY = shift;
    'not-used',       # $SSTATE = shift;
    'not-used',       # $SZIP = shift;
    'INV00001',       # $ORDERID = shift;
    $handling,        # $HANDLING = shift;
    $uspsfee,         # $USPSFEE = shift;
    'not-used',       # $STREET2 = shift; $STREET2 =~ s/[&]/ and /g; # FILTER $STREET2 =~ s/[^\w\s]//g;  # FILTER
    'not-used',       # $SSTREET2 = shift; $SSTREET2 =~ s/[&]/ and /g; # FILTER $SSTREET2 =~ s/[^\w\s]//g;  # FILTER
    'not-used',       # $EMAIL = shift;
    $insurance,       # $INSURANCE = shift;
    $repackfee,       # $REPACKFEE = shift;
    $reshipfee,       # $RESHIPFEE = shift;
    $noapoidfee,      # $NOAPOIDFEE = shift;
  );

  print $result . "\n";
  print $paypal_id . "\n";
  print $message . "\n";
}


sub ChargeCC
{
use REST::Client;
use JSON;

print "\nStarting credit card charge...\n";

# New inputs
my $billing_id = shift;
my $billing_address_id = shift;
my $shipping_address_id = shift;

# Existing inputs (several of these are no longer needed, see testChargeCC)
my $TOTAL = shift;
$CCNUM = shift;
my $CCEXPYEAR = shift;
my $CCEXPMO = shift;
my $CCFNAME = shift;
my $CCLNAME = shift;
my $COUNTRY = shift;
$STREET = shift;
$STREET =~ s/[&]/ and /g; # FILTER
$STREET =~ s/[^\w\s]//g;  # FILTER
#print "CC STREET: $STREET\n";
my $CITY = shift;
my $STATE = shift;
my $ZIP = shift;
my $PHONE = shift;
$PHONE =~ s/-//g;
$PHONE =~ s/ //g;
$PHONE =~ s/\)//g;
$PHONE =~ s/\(//g;
$CCCVV = shift;
$SFNAME = shift;
$SFNAME =~ s/[&]/ and /g; # FILTER
$SFNAME =~ s/[^\w\s]//g;  # FILTER
$SLNAME = shift;
$LFNAME =~ s/[&]/ and /g; # FILTER
$LFNAME =~ s/[^\w\s]//g;  # FILTER
my $COUNTRY = shift;
$SSTREET = shift;
$SSTREET =~ s/[&]/ and /g; # FILTER
$SSTREET =~ s/[^\w\s]//g;  # FILTER
my $SCITY = shift;
my $SSTATE = shift;
my $SZIP = shift;
my $ORDERID = shift;
my $HANDLING = shift;
my $USPSFEE = shift;
$STREET2 = shift;
$STREET2 =~ s/[&]/ and /g; # FILTER
$STREET2 =~ s/[^\w\s]//g;  # FILTER
$SSTREET2 = shift;
$SSTREET2 =~ s/[&]/ and /g; # FILTER
$SSTREET2 =~ s/[^\w\s]//g;  # FILTER
my $EMAIL = shift;
my $INSURANCE = shift;
my $REPACKFEE = shift;
my $RESHIPFEE = shift;
my $NOAPOIDFEE = shift;

$INSURANCE = sprintf "%.2f", $INSURANCE;
$USPSFEE = sprintf "%.2f", $USPSFEE;
$HANDLING = sprintf "%.2f", $HANDLING;
$TOTAL = sprintf "%.2f", $TOTAL;
$REPACKFEE = sprintf "%.2f", $REPACKFEE;
$RESHIPFEE = sprintf "%.2f", $RESHIPFEE;
$NOAPOIDFEE = sprintf "%.2f", $NOAPOIDFEE;

$HANDLING_TOTAL = sprintf "%.2f", $HANDLING + $INSURANCE + $REPACKFEE + $RESHIPFEE + $NOAPOIDFEE;

my $items_json = '{
    "name": "APO Box Handling Fee",
    "sku": "HND-' . $HANDLING . '",
    "price": "' . $HANDLING . '"
},{
    "name": "Package Insurance",
    "sku": "INS-' . $insurance_amount_xml . '",
    "price": "' . $INSURANCE . '"
}';

if ($REPACKFEE > 1) {
    $items_json .= ',{
        "name": "Package Repacking",
        "sku": "RPK-30.00",
        "price": "' . $REPACKFEE . '"
    }';
}
if ($RESHIPFEE > 1) {
    $items_json .= ',{
        "name": "Package Reshipping",
        "sku": "RSHP-10.00",
        "price": "' . $RESHIPFEE . '"
    }';
}
if ($NOAPOIDFEE > 1) {
    $items_json .= ',{
        "name": "Address Correction",
        "sku": "ADDRC-3.00",
        "price": "' . $NOAPOIDFEE . '"
    }';
}

my $json_body = '{
  "payment": {
    "description": "APOBox Package Forwarding ' . $ref_id_xml . '",
    "billing_id": "' . $billing_id . '",
    "billing_address_id": "' . $billing_address_id . '",
    "shipping_address_id": "' . $shipping_address_id . '",
    "invoice_id": "' . $ORDERID . '",
    "total": "' . $TOTAL . '",
    "subtotal": "' . $HANDLING_TOTAL . '",
    "shipping_cost": "' . $USPSFEE . '",
    "items": [' . $items_json . ']
  }
}';

# Extra fields
#$ref_id; # was Custom

my $headers = {
    'Content-Type' => 'application/vnd.api+json',
    'Authorization' => 'Bearer ' . $bearerToken
};

print "Checking with cc gateway\n";
my $client = REST::Client->new();
$client->setHost($rest_api);
print Dumper($json_body);
$client->POST(
    '/payment',
    $json_body,
    $headers
);

if (BadResponseCode($client->responseCode())) {
  return ProcessBadResponse($client->responseCode());
}

my $response = from_json($client->responseContent());

if ($client->responseCode() == '400') {
    $message = '400 Bad request. ' . $response->{name} . ' - ' . $response->{message} . "\n";
    return ('failure', null, $message);
}

if ($response->{success} != 'true') {
   $result_text = "Problem Parsing Credit Gateway response";
   print "Problem Parsing Credit Gateway response - cc server not responding\n";
    status_box("$result_text", "CC Server Not Responding", "red", "close");
    return ($response->{code}, null, $response->{message});
}

print "Done parsing CC data\n";
return ($response->{state}, $response->{id}, $response->{message});
}

sub BadResponseCode {
  my $code = shift;

  if (
    $code == '401'
    || $code == '404'
  ) {
    return 1;
  }

  return 0;
}

sub ProcessBadResponse {
  my $code = shift;

  my $message = null;

  if ($code == '401') {
    $message = "401 Unathorized. Failed to authenticate with API. Check your bearer token.\n";
  }
  if ($code == '404') {
    $message = "404 Not Found.\n";
  }

  if ($message) {
    return ('failure', null, $message);
  }
}
