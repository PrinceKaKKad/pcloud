<script type="text/javascript">
  function fetchStatus() {
    var auth = "<?php echo $auth ?>";
    var url = "api/ishardwareconnected?token=" + auth;

    $.ajax({
      url: url,
      type: "GET",
      dataType: "json", // Specify the data type as JSON
      success: function(response) {
        var statusElement = $("#status");

        if (response === true) {
          statusElement.html('<div class="col-md-12 fade-in">Status: <label class="online">online</label></div>');
        } else {
          statusElement.html('<div class="col-md-12 fade-in">Status: <label class="offline">offline</label></div>');
        }
      },
      error: function() {
        var statusElement = $("#status");
        statusElement.text("Error fetching status");
      }
    });
  }

  // Fetch initial status
  fetchStatus();

  // Periodically update status every 1 second
  setInterval(fetchStatus, 1000);
</script>