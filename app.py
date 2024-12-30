from flask import Flask, request, send_file
import qrcode
import io

app = Flask(__name__)

@app.route('/generate_qr', methods=['POST'])
def generate_qr():
    # Obtener los datos enviados en el cuerpo de la solicitud
    data = request.form['data']
    
    # Crear el código QR
    img = qrcode.make(data)
    
    # Guardar el QR en memoria (sin archivo temporal)
    img_io = io.BytesIO()
    img.save(img_io)
    img_io.seek(0)
    
    # Devolver el archivo QR como respuesta
    return send_file(img_io, mimetype='image/png', as_attachment=True, download_name='compra_qr.png')

if __name__ == '__main__':
    app.run(debug=True)
