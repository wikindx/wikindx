export function displayError(error) {
  document.getElementById("wikindx-display-results").style.display = "none";
  document.getElementById("wikindx-finalize").style.display = "none";
  document.getElementById("wikindx-success").style.display = "none";

  var displayErr = document.getElementById("wikindx-error");
  displayErr.innerHTML = error;
  displayErr.style.display = "block";
  document.getElementById("wikindx-messages").style.display = "block";
}
export function displaySuccess(success) {
  document.getElementById("wikindx-display-results").style.display = "none";
  document.getElementById("wikindx-finalize").style.display = "none";
  document.getElementById("wikindx-error").style.display = "none";

  var displaySucc = document.getElementById("wikindx-success");
  displaySucc.innerHTML = success;
  displaySucc.style.display = "block";
  document.getElementById("wikindx-messages").style.display = "block";
}
