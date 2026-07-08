/**
 * Bengkel Management System – Main Script
 * Requires jQuery 3.x and Bootstrap 5 JS
 */
$(function () {

    var $sidebarTooltips = $('#sidebar [data-bs-toggle="tooltip"]');
    $sidebarTooltips.tooltip({
        placement: 'right',
        trigger: 'hover'
    });


    var SIDEBAR_KEY = "sidebar_state";

    function getSidebarState() {
        return localStorage.getItem(SIDEBAR_KEY) || "open";
    }

    function setSidebarState(state) {
        localStorage.setItem(SIDEBAR_KEY, state);
    }

    function applySidebarState(state) {
        if (state === 'closed') {
            $('#sidebar').addClass('collapsed');
            $('#main-content').addClass('expanded');
            $('.navbar').addClass('collapsed');

            // Ubah Ikon menjadi Hamburger
            $('#sidebar-toggle i').removeClass('bx-x').addClass('bx-menu');

            // Nyalakan Tooltip
            $sidebarTooltips.tooltip('enable');
        } else {
            $('#sidebar').removeClass('collapsed');
            $('#main-content').removeClass('expanded');
            $('.navbar').removeClass('collapsed');

            // Ubah Ikon menjadi Silang (X)
            $('#sidebar-toggle i').removeClass('bx-menu').addClass('bx-x');

            // Matikan dan Sembunyikan Tooltip
            $sidebarTooltips.tooltip('disable');
            $sidebarTooltips.tooltip('hide');
        }
    }

    // Terapkan state saat halaman pertama kali dimuat
    applySidebarState(getSidebarState());
    document.documentElement.classList.remove('sidebar-pre-closed');

    // Aksi saat tombol toggle diklik
    $('#sidebar-toggle').on('click', function () {
        var isNowCollapsed = $('#sidebar').toggleClass('collapsed').hasClass('collapsed');

        $('#main-content').toggleClass('expanded', isNowCollapsed);
        $('.navbar').toggleClass('collapsed', isNowCollapsed);
        setSidebarState(isNowCollapsed ? 'closed' : 'open');

        var $icon = $(this).find('i');
        if (isNowCollapsed) {
            $icon.removeClass('bx-x').addClass('bx-menu');
            $sidebarTooltips.tooltip('enable');
        } else {
            $icon.removeClass('bx-menu').addClass('bx-x');
            $sidebarTooltips.tooltip('disable');
            $sidebarTooltips.tooltip('hide');
        }
    });

    // Toggle sidebar untuk tampilan Mobile
    $('#mobile-sidebar-toggle').on('click', function (e) {
        e.stopPropagation();
        $('#sidebar').toggleClass('mobile-open');
    });

    // Sembunyikan sidebar mobile jika user mengklik area luar sidebar
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#sidebar, #mobile-sidebar-toggle').length) {
            $('#sidebar').removeClass('mobile-open');
        }
    });


    setTimeout(function () {
        $(".alert-auto").fadeOut(500, function () {
            $(this).remove();
        });
    }, 4000);


    $(document).on("click", ".btn-confirm", function (e) {
        const msg = $(this).data("confirm") || "Apakah Anda yakin ingin melakukan aksi ini?";
        if (!confirm(msg)) e.preventDefault();
    });

    const YEAR_NOW = new Date().getFullYear();
    $("#tahun").on("input blur", function () {
        const v = parseInt($(this).val(), 10);
        const err = $("#tahun-error");
        if ($(this).val() !== "" && (isNaN(v) || v < 1980 || v > YEAR_NOW)) {
            $(this).addClass("is-invalid");
            err.text("Tahun harus antara 1980 dan " + YEAR_NOW + ".");
        } else {
            $(this).removeClass("is-invalid");
            err.text("");
        }
    });

    const $tgl = $("#tanggal_masuk");
    if ($tgl.length) {
        // Set max = 7 hari ke depan
        const maxDate = new Date();
        maxDate.setDate(maxDate.getDate() + 7);
        $tgl.attr("max", maxDate.toISOString().split("T")[0]);
    }

    $(document).on("click", "#btn-add-sparepart", function () {
        const id_servis = $(this).data("id-servis");
        const id_sp = $("#sp-select").val();
        const qty = parseInt($("#sp-qty").val(), 10);
        const stok = parseInt($("#sp-select option:selected").data("stok"), 10);

        if (!id_sp) {
            showToast("Pilih sparepart terlebih dahulu.", "warning");
            return;
        }
        if (!qty || qty < 1) {
            showToast("Qty minimal 1.", "warning");
            return;
        }
        if (qty > stok) {
            showToast("Qty melebihi stok tersedia (" + stok + ").", "danger");
            return;
        }

        // Cek duplikat
        let isDup = false;
        $("#sparepart-tbody tr").each(function () {
            if ($(this).data("id-sp") == id_sp) {
                isDup = true;
            }
        });
        if (isDup) {
            showToast("Sparepart sudah ada. Edit qty-nya langsung di tabel.", "warning");
            return;
        }

        $.post(
            BASE_URL + "servis/tambah_sparepart.php",
            { id_servis: id_servis, id_sparepart: id_sp, qty: qty },
            function (res) {
                if (res.success) {
                    appendSparepartRow(res.row);
                    updateTotalSparepart();
                    $("#sp-qty").val(1);
                    showToast("Sparepart ditambahkan.", "success");
                } else {
                    showToast(res.message || "Gagal menambahkan sparepart.", "danger");
                }
            },
            "json"
        ).fail(function () {
            showToast("Koneksi ke server gagal.", "danger");
        });
    });

    function appendSparepartRow(row) {
        const tr = $("<tr>")
            .attr("data-id-sp", row.id_sparepart)
            .attr("data-id-detail", row.id_detail);
        tr.append("<td>" + escHtml(row.nama_part) + "</td>");
        tr.append('<td class="text-end">' + row.qty + "</td>");
        tr.append('<td class="text-end">' + rupiah(row.harga_satuan) + "</td>");
        tr.append('<td class="text-end fw-semibold">' + rupiah(row.subtotal) + "</td>");
        tr.append('<td><button type="button" class="btn btn-danger btn-sm btn-del-sp" data-id="' + row.id_detail + '"><i class="bx bx-trash"></i></button></td>');
        $("#sparepart-tbody").append(tr);
    }

    // Hapus baris sparepart via AJAX
    $(document).on("click", ".btn-del-sp", function () {
        if (!confirm("Hapus sparepart ini dari servis?")) return;
        const id_detail = $(this).data("id");
        const $row = $(this).closest("tr");

        $.post(
            BASE_URL + "servis/hapus_sparepart.php",
            { id_detail: id_detail },
            function (res) {
                if (res.success) {
                    $row.fadeOut(200, function () {
                        $(this).remove();
                        updateTotalSparepart();
                    });
                    showToast("Sparepart berhasil dihapus.", "success");
                } else {
                    showToast(res.message || "Gagal menghapus sparepart.", "danger");
                }
            },
            "json"
        ).fail(function () {
            showToast("Koneksi ke server gagal.", "danger");
        });
    });

    // Update live stok info saat pilih sparepart
    $(document).on("change", "#sp-select", function () {
        const opt = $(this).find(":selected");
        const stok = opt.data("stok") || 0;
        const harga = opt.data("harga") || 0;

        $("#sp-stok-info").text(stok > 0 ? "Stok: " + stok : "Stok habis");
        $("#sp-harga-info").text(stok > 0 ? "Harga: " + rupiah(harga) + "/unit" : "");
        $("#sp-qty").attr("max", stok);

        if (stok === 0) $("#sp-qty").val(0);
    });

    function updateTotalSparepart() {
        let total = 0;
        $("#sparepart-tbody tr").each(function () {
            const raw = $(this).find("td:eq(3)").text().replace(/[^0-9]/g, "");
            total += parseInt(raw, 10) || 0;
        });
        $("#total-sparepart").text(rupiah(total));
    }

    $(document).on("click", ".photo-item img, .photo-item", function (e) {
        const src = $(this).is("img") ? $(this).attr("src") : $(this).find("img").attr("src");
        if (src) {
            $("#lightbox-img").attr("src", src);
            const photoModal = new bootstrap.Modal(document.getElementById("photoModal"));
            photoModal.show();
        }
    });


    $("#foto-input").on("change", function () {
        const files = this.files;
        const MAX_MB = 2;
        let invalid = [];

        Array.from(files).forEach(function (f) {
            const ext = f.name.split(".").pop().toLowerCase();
            if (!["jpg", "jpeg", "png", "gif"].includes(ext) || f.size > MAX_MB * 1024 * 1024) {
                invalid.push(f.name);
            }
        });

        if (invalid.length) {
            showToast("File tidak valid (maks 2MB, format jpg/png/gif): " + invalid.join(", "), "danger");
            $(this).val("");
            $("#foto-names").text("");
            return;
        }

        if (files.length) {
            $("#foto-names").text(files.length + " file dipilih: " + Array.from(files).map((f) => f.name).join(", "));
        }
    });

    $("form").on("submit", function () {
        const $btn = $(this).find("[type=submit]");
        setTimeout(function () {
            $btn.prop("disabled", true).html('<i class="bx bx-loader-alt bx-spin"></i> Memproses...');
        }, 50);
    });

    window.rupiah = function (n) {
        return "Rp\u00a0" + parseInt(n || 0).toLocaleString("id-ID");
    };

    window.escHtml = function (str) {
        return $("<div>").text(str).html();
    };

    window.showToast = function (msg, type) {
        type = type || "info";
        const colors = {
            success: "#10b981",
            danger: "#ef4444",
            warning: "#f59e0b",
            info: "#3b82f6",
        };
        const $t = $('<div class="toast-msg">')
            .css("background", colors[type] || colors.info)
            .text(msg);

        $("body").append($t);

        setTimeout(function () {
            $t.fadeOut(300, function () {
                $(this).remove();
            });
        }, 3500);
    };

});
