var twilio = require('twilio'),
    http = require('http');

var qs = require('querystring');
 
http.createServer(function (req, res) {
    var body = '';
    req.on('data', function(data) {
      body += data;
    });

    req.on('end', function() {
      var parsed_body = qs.parse(body);
      var zip = parsed_body['Body'];

      var options = {
        host: 'api.rovicorp.com',
        port: 80,
        path: '/TVlistings/v9/listings/services/postalcode/'+zip+'/info?locale=en-US&countrycode=US&apikey=bnp966tdms7t9p5hze264wae&sig=sig',
        method: 'GET'
      };
      var get_providers_req = http.get(options, function(res1) {
        var page_data = "";
        res1.setEncoding('utf8');
        res1.on('data', function (chunk) {
          page_data += chunk;
        });
        parsed_page_data = qs.parse(page_data);
        res1.on('end', function () {
          var json = JSON.parse(page_data);
          var service_id = json.ServicesResult.Services.Service[0].ServiceId;
          console.log(service_id);

          var resp = new twilio.TwimlResponse();
          resp.sms(service_id);
          res.writeHead(200, {
              'Content-Type':'text/xml'
          });
          res.end(resp.toString());
        });
      });
      get_providers_req.end();
    });
}).listen(1337);
 
console.log('Visit http://localhost:1337/ in your browser to see your TwiML document!');
