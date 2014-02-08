var twilio = require('twilio'),
    http = require('http');
 
http.createServer(function (req, res) {
    var resp = new twilio.TwimlResponse();
    resp.sms('ahoy hoy! Testing Twilio and node.js');
    res.writeHead(200, {
        'Content-Type':'text/xml'
    });
    res.end(resp.toString());
}).listen(1337);
 
console.log('Visit http://localhost:1337/ in your browser to see your TwiML document!');
