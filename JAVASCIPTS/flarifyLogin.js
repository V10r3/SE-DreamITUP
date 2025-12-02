document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const usernameInput = document.querySelector("input[type='text']");
    const passwordInput = document.querySelector("input[type='password']");
    const rememberCheckbox = document.querySelector("input[type='checkbox']");

    form.addEventListener("submit", (event) => {
        event.preventDefault(); 

        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();
        const remember = rememberCheckbox.checked;

        if (username === "" || password === "") {
            alert("Please fill in both fields.");
            return;
        }

    
        if (username === "admin" && password === "1234") {
            alert("Login successful! 🎉");
            
            if (remember) {
                localStorage.setItem("flarifyUser", username);
            }

            window.location.href = "dashboard.html";
        } else {
            alert("Invalid username or password ❌");
        }
    });
});
