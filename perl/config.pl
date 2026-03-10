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
#$ENV{'PERL_LWP_SSL_VERIFY_HOSTNAME'} = 0;


# Global Configuration
our $rest_api = 'https://api.apobox.com';
#our $rest_api = 'https://account.apobox.com/api';
#our $rest_api = 'https://c3.propic.com:3000';
#our $rest_api = 'https://apobox.dev/api';
#our $bearerToken = 'B34R3R70K3N';

require 'local.pl' if -e 'local.pl';

# Libraries
use Data::Dumper;

1;
