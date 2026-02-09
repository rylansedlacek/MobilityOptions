#!/usr/bin/env python3


SMTP_SERVER = os.getenv("SMTP_SERVER")
SMTP_PORT = int(os.getenv("SMTP_PORT", "587"))
SMTP_USER = os.getenv("SMTP_USER")
SMTP_PASS = os.getenv("SMTP_PASS")




def output_json(obj):
print(json.dumps(obj))
sys.stdout.flush()




def error(msg):
output_json({"success": False, "sent": [], "failed": [], "error": msg})
sys.exit(1)


# Read JSON input
try:
raw_input = sys.stdin.read().strip()
if not raw_input:
error('No input received')
data = json.loads(raw_input)
emails = data.get("emails")
# Accept either 'sender' or 'senderName'
sender_name = data.get("senderName") or data.get("sender") or "No Name"
subject = data.get("subject")
body = data.get("body")
if not emails or not isinstance(emails, list) or not subject:
error('Missing required fields: emails (list) and subject')
except Exception as e:
error(f"Invalid JSON input: {str(e)}")




def build_message(sender_name, to_email, subject, body):
msg = MIMEText(body, "plain")
msg["From"] = f"{sender_name} <{SMTP_USER}>"
msg["To"] = to_email
msg["Subject"] = subject
return msg.as_string()


# Connect to SMTP
try:
server = smtplib.SMTP(SMTP_SERVER, SMTP_PORT, timeout=30)
server.set_debuglevel(0)
server.ehlo()
server.starttls()
server.ehlo()
server.login(SMTP_USER, SMTP_PASS)
except Exception as e:
error(f"SMTP connection/login failed: {str(e)}")


sent = []
failed = []


for to_email in emails:
try:
msg = build_message(sender_name, to_email, subject, body)
server.sendmail(SMTP_USER, to_email, msg)
sent.append(to_email)
except Exception as e:
failed.append({"email": to_email, "error": str(e)})


try:
server.quit()
except Exception:
pass


# Return JSON result
output_json({
"success": len(failed) == 0,
"sent": sent,
"failed": failed,
"error": ""
})