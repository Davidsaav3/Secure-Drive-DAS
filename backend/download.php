<?php
require_once("functions.php");
$keys = generateRSA();

    $file_name = "declarasion.PNG";

    // Directorio donde se guardó el archivo cifrado
    $source = "storage/" . $file_name;

    // Verificar si el archivo existe
    if (file_exists($source)) {
        // Descifrar el archivo
        $decryptedContent = AESDecode(file_get_contents($source), " MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDN41ac4ynKVVpT Bg16ulaL/V6bC32UAzDja62y/xRQEb3KOwY7PFF3s69Wnz0w3fL+vBmyQ8Fdm1hY JGvWcLtsWfWX3sqHL2SpEzxfEQONkPiH2zK847hj3jR2hrbPLHgf8ELx1RnAUqrm R3MberZyi9nLUczTzbX0UfMRrEgIivRtTe50gRs3I8wc4CJWa8qT70tZbBOH9CLQ ophgmfQP1D/Tl34xIUr7FhsQ+qA7ZCi8LVMxXEoG7guPF9uUjnzMEFShdlEijDzF TKo2tsgYCNywlK/75GtgOMDHNwJrR1iz1o8PSO9vcBgcUGi+E8wMFhB+PPndwOpU FQ+rPd7tAgMBAAECggEAAqNtADdZrpSXwuRFzEf9WkTlxKHfOu7W3mCKX3atBCD+ EsyH1hQoom/kiX0ik8Y8Whmp+01VTzBG77Y5GuHjR6Otey6T34CdeccxKQdhnsZN UQcKPZPDe1AlJvcEpQoCWL0Y9oVkaOuw8fYsxPI2dVL9IwG02L/+8BcUFiq5hOvR CmhBICQaAzoD568xUSVRIiLevuVMTpEFVh+K9L0rt4yrRpnBP5//TqIVgbyYpIJM SwSFENY+TQLseXdUjZ1QAuWgqhxNK+9QkCEJBOyPRFD80BQvP62FRHXURIZV69bb KxjAucMb3tecEyUYTndiNwFOfUT8UzjFx45ALmKrsQKBgQDyrPRlIn71iAvY7+iq 8iMj2A32DhHT+xm/fulIX+R3s5In1NT2eqz0NG5kvgBxQKlftooFKbP7S7jVrooY q2Y0rS0pota/WZol55Q4pxbg2ypb26MIzulCPie+5oXO/CtiU6JX8yjjTX5j2FpZ MBBkrAabR6+9YKPn0RIq9UyUNQKBgQDZMUxRQ9DSHQY65oXea48H74vq1I9gxC4X X+JWeHcpvLfo+GZrC5POog8OHegiWRca7EsIhfaA2QMSTB0spnEykRpFcQsv929s O8dvsZaZSrY6McNfYZUc2CsaDdNBgQ9cWm+1tUxOzU+qYfw5Cu645fj0GP3W3mxq aC1/D88G2QKBgFZ3R5EtbF0hv07plFYsdlbUKY1NUA7evjrcBlTSTf5UjjQBAmxc I3nToK3mgRPZPUAsMxtJ06YrQc1pJi1KDN2iAqB+M9P0Ihd1XvuclWtCy+H07S87 QiNnMBQ14OFyOicLs6Fws1XiC7GZqf9zP7QPEz+KmFR6tYvok3eY9VMtAoGAdbVQ BSLZw2XTgIx6tMZaKBTdIZG9etYXnLdsdSyoeEg869fjudP2cSBHRIFU03ixGvhA 2gewRrhV/86caRxzcNJPCJ9xTres/V2QgqoeUkm5ZOSfW8wJAi7tfRtNCM0nRAgH TtVI29RNfqvIBCo2oqKQP8pjl9XHsAtxzNEGhFECgYEArBsPMuxKiHvJO06hS+3Z /V4burzzSFijL7YNEbBubQ8EwipTn/L7y79rWLnmGzMmSIIlnQ70JGHaCpmsJrkA hBSolc5owz8rvm/mt8c5hmjjifWD2ieVEkhBAtnAa+8QfmuuyMUyk3Io8Xq6YP+j Fifsy/NS2/xz/C2AfRF5biI= ");

        // Establecer las cabeceras para la descarga del archivo
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($decryptedContent));

        // Enviar el contenido descifrado al navegador
        echo $decryptedContent;
        exit;
    } else {
        echo "El archivo no existe en el servidor.";
    }



?>