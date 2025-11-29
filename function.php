<?php


$conn = mysqli_connect("localhost", "root", "", "simbs");


// fungsi untuk menampilkan data dari database
function query($query){
    global $conn;


    $result = mysqli_query($conn, $query);
    $rows = [];
    while( $row = mysqli_fetch_assoc($result) ) {
        $rows[] = $row;
    }
    return $rows;
}

// fungsi untuk menambahkan data ke database
function tambah_data($data){
    global $conn;

    $judul = $data['judul'];
    $penulis = $data['penulis'];
    $halaman = $data['halaman'];
    $tahun_terbit = $data['tahun_terbit'];
    $penerbit = $data['penerbit'];

    // upload gambar
    $gambar = upload_gambar($judul); 
    if( !$gambar ) {
        return false;
    }

    $kategori = $data['kategori'];
    $genre = $data['genre'];

    $query = "INSERT INTO buku (judul, penulis, halaman, tahun_terbit, penerbit, gambar, kategori, genre)
                  VALUES ('$judul', '$penulis', '$halaman', '$tahun_terbit', '$penerbit', '$gambar', '$kategori', '$genre')
                 ";
    mysqli_query($conn, $query);


    return mysqli_affected_rows($conn);    
}


// fungsi untuk menghapus data dari database
function hapus_data($id_buku){
    global $conn;


    $query = "DELETE FROM buku WHERE id_buku = $id_buku";


    $result = mysqli_query($conn, $query);


    return mysqli_affected_rows($conn);    
}

// fungsi untuk mengubah data dari database
function ubah_data($data){
    global $conn;

    $judul = $data['judul'];
    $penulis = $data['penulis'];
    $halaman = $data['halaman'];
    $tahun_terbit = $data['tahun_terbit'];
    $penerbit = $data['penerbit'];
    // $gambar = $data['gambar'];

    // upload gambar
    $gambar = upload_gambar($judul); 
    if( !$gambar ) {
        return false;
    }

    $kategori = $data['kategori'];
    $genre = $data['genre'];

    $query = "UPDATE buku SET
                judul = '$judul',
                penulis = '$penulis',
                halaman = '$halaman',
                tahun_terbit = '$tahun_terbit',
                penerbit = '$penerbit',
                gambar = '$gambar',
                kategori = '$kategori',
                genre = '$genre'
              WHERE id_buku = $id_buku
             ";


     $result = mysqli_query($conn, $query);
     
     return mysqli_affected_rows($conn);
}

// fungsi untuk mencari data
function search_data($keyword){
    global $conn;


    $query = "SELECT * FROM buku
              WHERE
              id_buku LIKE '%$keyword%' OR
              judul LIKE '%$keyword%' OR
              penulis LIKE '%$keyword%' OR
              halaman LIKE '%$keyword%' OR
              tahun_terbit LIKE '%$keyword%' OR
              penerbit LIKE '%$keyword%' OR
              gambar LIKE '%$keyword%' OR
              kategori LIKE '%$keyword%' OR
              genre LIKE '%$keyword%' 
            ";
    return query($query);
}

// fungsi untuk upload gambar
function upload_gambar($judul) {


    // setting gambar
    $namaFile = $_FILES['gambar']['name'];
    $ukuranFile = $_FILES['gambar']['size'];
    $error = $_FILES['gambar']['error'];
    $tmpName = $_FILES['gambar']['tmp_name'];


    // cek apakah tidak ada gambar yang diupload
    if( $error === 4 ) {
        echo "<script>
                alert('pilih gambar terlebih dahulu!');
              </script>";
        return false;
    }


    // cek apakah yang diupload adalah gambar
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));
    if( !in_array($ekstensiGambar, $ekstensiGambarValid) ) {
        echo "<script>
                alert('yang anda upload bukan gambar!');
              </script>";
        return false;
    }


    // cek jika ukurannya terlalu besar
    // maks --> 5MB
    if( $ukuranFile > 5000000 ) {
        echo "<script>
                alert('ukuran gambar terlalu besar!');
              </script>";
        return false;
    }


    // lolos pengecekan, gambar siap diupload
    // generate nama gambar baru
    $namaFileBaru = $judul;
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiGambar;


    move_uploaded_file($tmpName, 'img/' . $namaFileBaru);


    return $namaFileBaru;
}

// fungsi untuk register
function register($data){
    global $conn;


    $username = strtolower($data['username']);
    $email = $data['email'];
    $password = mysqli_real_escape_string($conn, $data['password']);

    // untuk mematikan password tidak lebih atau kurang dari 8
    if (strlen($password) < 8){
        return "Password kurang dari 8 karakter!";
    }
    if (strlen($password) > 8){
        return "Password melebihi 8 karakter!";
    }

    // query untuk ngecek username yang diinputkan oleh user di database
    $query = mysqli_query($conn, "SELECT username FROM user WHERE username = '$username'");
    $result = mysqli_fetch_assoc($query);


    if($result != NULL){
        return "Username sudah terdaftar!";
    }

    // enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT);


    // tambahkan userbaru ke database
    mysqli_query($conn, "INSERT INTO user (username, email, password) VALUES('$username', '$email', '$password')");


    return true;
}

// fungsi untuk login
function login($data){
    global $conn;


    $username = $data['username'];
    $password = $data['password'];


    $query = "SELECT * FROM user WHERE username = '$username'";
    $result = mysqli_query($conn, $query);


    if(mysqli_num_rows($result) === 1){
        $row = mysqli_fetch_assoc($result);


        if(password_verify($password, $row['password'])){
            $_SESSION['login'] = true;
            $_SESSION['username'] = $row['username'];
           
            return true;
        } else {
           
            return "Password salah!";
        }


    }else{
        return "Username tidak terdaftar!";
    }
}


?>


