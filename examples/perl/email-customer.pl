# Requirements
#
# The changes require installation of:
#   Data::Dumper
#   REST::Client
#   JSON
#   Mozilla::CA  - for verifying ssl certificates
#
# Easy installation of these modules can be done with:
#   sudo cpan App::cpanminus
#   sudo cpanm REST::Client
#   sudo cpanm JSON

# Testing
#
# While testing in development, NOT IN PRODUCTION, and are getting "certificate
# verify failed" errors, you can uncomment this line to allow testing a server
# without a publically verifiable SSL certificate.
#
# $ENV{'PERL_LWP_SSL_VERIFY_HOSTNAME'} = 0;


# Global Configuration
our $rest_api = 'https://api.apobox.com';
our $bearerToken = 'replace_with_valid_token';


use Data::Dumper;

testSendOrderUpdate();

# Test method
sub testSendOrderUpdate {

  # Setup test input
  my $customer_id = '1';

  # Make test request to sendOrderUpdate
  my ($result, $message) = sendOrderUpdate(
    $customer_id,
  );

  print $result . "\n";
  print $message . "\n";
}


sub sendOrderUpdate {
use REST::Client;
use JSON;

my $customer_id = shift;

my $json_body = '{
  "message": {
    "subject": "Optional test subject: ' . $customer_id . '",
    "body": "Optional message body."
  }
}';

my $headers = {
    'Content-Type' => 'application/vnd.api+json',
    'Accept' => 'application/vnd.api+json',
    'Authorization' => 'Bearer ' . $bearerToken
};

print "Sending order update email\n";
my $client = REST::Client->new();
$client->setHost($rest_api);
print Dumper($json_body);
$client->POST(
    '/customers/' . $customer_id . '/notify',
    $json_body,
    $headers
);

if (BadResponseCode($client->responseCode())) {
  return ProcessBadResponse($client->responseCode());
}

my $response = '';
eval {
	$response = from_json($client->responseContent());
};


#if ($response->{success} != 'true') {
#   $result_text = "Problem Parsing Credit Gateway response";
#   print "Problem Parsing Credit Gateway response - cc server not responding\n";
#    status_box("$result_text", "CC Server Not Responding", "red", "close");
#    return ($response->{code}, null, $response->{message});
#}

print "Done sending email\n";
return ($response->{state}, $response->{message});
}

sub BadResponseCode {
  my $code = shift;

  if (
    $code == '400'
    || $code == '401'
    || $code == '404'
  ) {
    return 1;
  }

  return 0;
}

sub ProcessBadResponse {
  my $code = shift;

  my $message = null;

  if ($code == '400') {
    $message = "400 Bad Request.\n";
  }
  if ($code == '401') {
    $message = "401 Unathorized. Failed to authenticate with API. Check your bearer token.\n";
  }
  if ($code == '404') {
    $message = "404 Not Found.\n";
  }

  if ($message) {
    return ('failure', $message);
  }
}

