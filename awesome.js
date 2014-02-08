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

      var resp = new twilio.TwimlResponse();
      resp.sms(parsed_body['Body']);
      res.writeHead(200, {
          'Content-Type':'text/xml'
      });
      res.end(resp.toString());
    });

}).listen(1337);
 
console.log('Visit http://localhost:1337/ in your browser to see your TwiML document!');
