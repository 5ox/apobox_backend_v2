#############################################################################
# Generate and return API headers
#############################################################################
sub apiHeaders {
  return {
    'Content-Type' => 'application/vnd.api+json',
    'Authorization' => 'Bearer ' . $bearerToken,
    'Accept' => 'application/vnd.api+json',
  };
}

#############################################################################
# Return true is the provided response code is not a success code
#############################################################################
sub BadResponseCode {
  my $code = shift;

  if ($code =~ /^20[0124]/) {
    return 0;
  }

  return 1;
}

#############################################################################
# Return a formatted failure response
#############################################################################
sub ProcessBadResponse {
  my $code = shift;
  my $message = shift;

print Dumper($message);
  if ($code == '400') {
	my $response = from_json($message);
    $message = $response->{errors}[0]->{code} . ' - ' . $response->{errors}[0]->{title};
    $message = "400 Bad Request. " . $message . "\n";
  }
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

1;
