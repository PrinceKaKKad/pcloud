<footer>
<script src="../assets/js/lucide.js"></script>
<script>
  lucide.createIcons()
</script>
<script  src="assets/js/script.js"></script>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script type="text/javascript">
var showGetPopupButton = document.getElementById("showGetPopup");
var showPutPopupButton = document.getElementById("showPutPopup");
var showHardwarePopupButton = document.getElementById("showHardwarePopup");
var closePopupButtons = document.getElementsByClassName("closePopup");

var getPopupContainer = document.getElementById("getPopupContainer");
var putPopupContainer = document.getElementById("putPopupContainer");
var hardwarePopupContainer = document.getElementById("hardwarePopupContainer");

showGetPopupButton.addEventListener("click", function() {
  getPopupContainer.style.display = "block";
});

showPutPopupButton.addEventListener("click", function() {
  putPopupContainer.style.display = "block";
});

showHardwarePopupButton.addEventListener("click", function() {
  hardwarePopupContainer.style.display = "block";
});

Array.from(closePopupButtons).forEach(function(button) {
  button.addEventListener("click", function() {
    getPopupContainer.style.display = "none";
    putPopupContainer.style.display = "none";
    hardwarePopupContainer.style.display = "none";
  });
});

</script>

