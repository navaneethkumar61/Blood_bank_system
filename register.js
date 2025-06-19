emailjs.init("QgESPXXKLSCPMHdsY");

let generatedOTP = "";
let isOtpVerified = false;

function generateOTP() {
  return Math.floor(100000 + Math.random() * 900000).toString();
}

function toggleRoleFields() {
  const role = document.getElementById("register-role").value;
  document.getElementById("donor-fields").style.display = role === "donor" ? "block" : "none";
  document.getElementById("patient-fields").style.display = role === "patient" ? "block" : "none";
}

document.getElementById("sendOtpBtn").addEventListener("click", () => {
  const email = document.getElementById("email").value.trim();
  if (!email) {
    document.getElementById("message").innerText = "Please enter your email.";
    return;
  }

  generatedOTP = generateOTP();
  const templateParams = {
    user_email: email,
    otp_code: generatedOTP
  };

  emailjs.send("service_m6uh8l6", "template_rodn6tf", templateParams)
    .then(() => {
      document.getElementById("message").innerText = "OTP sent to your email.";
    })
    .catch(() => {
      document.getElementById("message").innerText = "Failed to send OTP.";
    });
});

document.getElementById("verifyOtpBtn").addEventListener("click", () => {
  const enteredOTP = document.getElementById("otp").value.trim();
  if (enteredOTP === generatedOTP) {
    isOtpVerified = true;
    document.getElementById("otp_verified").value = "true";
    document.getElementById("message").innerText = "OTP verified successfully!";
  } else {
    isOtpVerified = false;
    document.getElementById("otp_verified").value = "false";
    document.getElementById("message").innerText = "Incorrect OTP.";
  }
});

document.getElementById("registerForm").addEventListener("submit", function (e) {
  e.preventDefault();

  if (!isOtpVerified) {
    document.getElementById("message").innerText = "Please verify OTP before registering.";
    return;
  }

  const formData = new FormData(this);

  fetch("register.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.text())
    .then(data => {
      if (data.includes("success")) {
        document.getElementById("message").innerText = "Registration successful!";
        this.reset();
        isOtpVerified = false;
        document.getElementById("otp_verified").value = "false";
      } else {
        document.getElementById("message").innerText = data;
      }
    })
    .catch(err => {
      document.getElementById("message").innerText = "Registration failed.";
      console.error(err);
    });
});
