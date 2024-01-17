import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable({
  providedIn: 'root',
})

export class DownloaderService {

  constructor(private http: HttpClient) {}

  downloadFile(fileName: string): Observable<any> {
    const url = `https://proteccloud.000webhostapp.com/downloader.php`; 
    const headers = new HttpHeaders();
    headers.append('Content-Type', 'application/json');
    const formData = new FormData();
    formData.append('file_name', fileName);
    return this.http.post(url, formData, { headers, responseType: 'blob' })
      .pipe(
        catchError((error: HttpErrorResponse) => {
          //console.error('Error al descargar el archivo:', error);
          return throwError('No se pudo descargar el archivo; inténtalo de nuevo más tarde.');
        })
      );
  }

  downloader(fileName: string): void {
    this.downloadFile(fileName).subscribe(
      (response: any) => {
        const blob = new Blob([response], { type: 'application/octet-stream' });
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = fileName;
        link.click();
      },
      (error: any) => {
        //console.error('Error al descargar el archivo:', error);
      }
    );
  }
}
