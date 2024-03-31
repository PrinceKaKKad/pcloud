<script>
$(document).ready(function () {
    function fetchSwitchStatus(switchPin) {
        var auth = "<?php echo $auth ?>";
        var url = "api/v2/get?token=" + auth + "&pin=" + switchPin;

        $.ajax({
            url: url,
            type: "GET",
            success: function (response) {
                var statusElement = $("#status-" + switchPin);
                var toggleButton = $("#toggle-" + switchPin);

                if (response[switchPin]) {
                    var switchData = response[switchPin];

                    statusElement.text("Value: " + switchData.value);

                    // If 'data' field is present, update gauge
                    if (switchData.data) {
                        const gaugeElement = document.querySelector(".gauge-" + switchPin);

                        function setGaugeValue(gauge, value) {
                            if (value < 0 || value > 1) {
                                return;
                            }
                            gauge.querySelector(".gauge-fill-" + switchPin).style.transform = `rotate(${value / 2}turn)`;
                            gauge.querySelector(".gauge-cover-" + switchPin).textContent = `${Math.round(value * 250)}V`;
                        }

                        // Use 'data' field in the response to update the gauge
                        const elementValue = switchData.data / 250;
                        setGaugeValue(gaugeElement, elementValue);
                    }

                    // Update the toggle button based on the switch value
                    toggleButton.prop("checked", switchData.value === "1");
                } else {
                    statusElement.text("No data found.");
                    toggleButton.prop("checked", false);
                }
            },
            error: function (xhr, status, error) {
                var statusElement = $("#status-" + switchPin);
                statusElement.text(xhr.responseText);
                var toggleButton = $("#toggle-" + switchPin);
                toggleButton.prop("checked", false);
            }
        });
    }

    function toggleSwitch(switchPin, value) {
        var auth = "<?php echo $auth ?>";
        var url = "api/v2/internal/put?token=" + auth + "&pin=" + switchPin + "&value=" + value;

        $.ajax({
            url: url,
            type: "PUT",
            success: function (response) {
                fetchSwitchStatus(switchPin);
            },
            error: function () {
                alert("Failed to toggle the switch.");
            }
        });
    }

    function fetchInitialSwitchStatuses() {
        $(".switch-data .card").each(function () {
            var switchPin = $(this).find(".text-gray-500").text().trim().replace("Switch: ", "");
            fetchSwitchStatus(switchPin);
        });
    }

    fetchInitialSwitchStatuses();

    $(".toggle-action").change(function (e) {
        var switchPin = $(this).data("switch");
        var value = $(this).is(":checked") ? "1" : "0";
        toggleSwitch(switchPin, value);
    });

    setInterval(function () {
        fetchInitialSwitchStatuses();
    }, 1000);
});

</script>

