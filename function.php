<?php
function loadData($file) {
    $words = explode("\n", file_get_contents('data/dict/' . $file));

    foreach ($words as $word) {
        $wordlist[$word] = true;
    }

    return $wordlist;
}

function checkDict($word) {
    static $wordlist = null;

    if ($wordlist === null) {
        $wordlist = loadData('rootwords.txt');
    }

    if (isset($wordlist[$word])) {
        return true;
    } else {
        return false;
    }
}

// Hapus Inflection Suffixes (?-lah?, ?-kah?, ?-ku?, ?-mu?, atau ?-nya?)
function removeInflectionSuffix($word) {
    $word0 = $word;
    if (preg_match('/([km]u|nya|[kl]ah|pun)$/', $word)) { // Cek Inflection Suffixes
        $word1 = preg_replace('/([km]u|nya|[kl]ah|pun)$/', '', $word);
        if (preg_match('/([klt]ah|pun)$/', $word)) { // Jika berupa particles (?-lah?, ?-kah?, ?-tah? atau ?-pun?)
            if (preg_match('/([km]u|nya)$/', $word1)) { // Hapus Possesive Pronouns (?-ku?, ?-mu?, atau ?-nya?)
                $word2 = preg_replace('/([km]u|nya)$/', '', $word1);
                return $word2;
            }
        }
        return $word1;
    }
    return $word0;
}

// Cek Prefix Disallowed Sufixes (Kombinasi Awalan dan Akhiran yang tidak diizinkan)
function checkPrefixDisallowedSufix($word) {
    if (preg_match('/^(be)[[:alpha:]]+(i)$/', $word)) { // be- dan -i
        return true;
    }
    if (preg_match('/^(di)[[:alpha:]]+(an)$/', $word)) { // di- dan -an
        return true;

    }
    if (preg_match('/^(ke)[[:alpha:]]+(i|kan)$/', $word)) { // ke- dan -i,-kan
        return true;
    }
    if (preg_match('/^(me)[[:alpha:]]+(an)$/', $word)) { // me- dan -an
        return true;
    }
    if (preg_match('/^(se)[[:alpha:]]+(i|kan)$/', $word)) { // se- dan -i,-kan
        return true;
    }
    return false;
}

// Hapus Derivation Suffixes (?-i?, ?-an? atau ?-kan?)
function removeDerivationSuffix($word) {
    $word0 = $word;
    if (preg_match('/(kan)$/', $word)) { // Cek Suffixes
        $word1 = preg_replace('/(kan)$/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
    }
    if (preg_match('/(an|i)$/', $word)) { // cek -kan
        $word2 = preg_replace('/(an|i)$/', '', $word);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    if (checkPrefixDisallowedSufix($word)) {
        return $word0;
    }
    return $word0;
}

// Hapus Derivation Prefix (?di-?, ?ke-?, ?se-?, ?te-?, ?be-?, ?me-?, atau ?pe-?)
function removeDerivationPrefix($word) {
    $word0 = $word;
    /* ------ Tentukan Tipe Awalan ------------*/
    if (preg_match('/^(di|[ks]e)\S{1,}/', $word)) { // Jika di-,ke-,se-
        $word1 = preg_replace('/^(di|[ks]e)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    if (preg_match('/^([^aiueo])e\\1[aiueo]\S{1,}/i', $word)) { // aturan  37
        $word1 = preg_replace('/^([^aiueo])e/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    if (preg_match('/^([tmbp]e)\S{1,}/', $word)) { //Jika awalannya adalah ?te-?, ?me-?, ?be-?, atau ?pe-?
        /*------------ Awalan ?be-?, ---------------------------------------------*/
        if (preg_match('/^(be)\S{1,}/', $word)) { // Jika awalan ?be-?,
            if (preg_match('/^(ber)[aiueo]\S{1,}/', $word)) { // aturan 1.
                $word1 = preg_replace('/^(ber)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(ber)/', 'r', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(ber)[^aiueor][[:alpha:]](?!er)\S{1,}/', $word)) { //aturan  2.
                $word1 = preg_replace('/^(ber)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(ber)[^aiueor][[:alpha:]]er[aiueo]\S{1,}/', $word)) { //aturan  3.
                $word1 = preg_replace('/^(ber)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^belajar\S{0,}/', $word)) { //aturan  4.
                $word1 = preg_replace('/^(bel)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(be)[^aiueolr]er[^aiueo]\S{1,}/', $word)) { //aturan  5.
                $word1 = preg_replace('/^(be)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }
        }
        /*------------end ?be-?, ---------------------------------------------*/
        /*------------ Awalan ?te-?, ---------------------------------------------*/
        if (preg_match('/^(te)\S{1,}/', $word)) { // Jika awalan ?te-?,

            if (preg_match('/^(terr)\S{1,}/', $word)) {
                return $word;
            }
            if (preg_match('/^(ter)[aiueo]\S{1,}/', $word)) { // aturan 6.
                $word1 = preg_replace('/^(ter)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(ter)/', 'r', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(ter)[^aiueor]er[aiueo]\S{1,}/', $word)) { // aturan 7.
                $word1 = preg_replace('/^(ter)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }
            if (preg_match('/^(ter)[^aiueor](?!er)\S{1,}/', $word)) { // aturan 8.
                $word1 = preg_replace('/^(ter)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }
            if (preg_match('/^(te)[^aiueor]er[aiueo]\S{1,}/', $word)) { // aturan 9.
                $word1 = preg_replace('/^(te)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(ter)[^aiueor]er[^aiueo]\S{1,}/', $word)) { // aturan  35 belum bisa
                $word1 = preg_replace('/^(ter)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }
        }
        /*------------end ?te-?, ---------------------------------------------*/
        /*------------ Awalan ?me-?, ---------------------------------------------*/
        if (preg_match('/^(me)\S{1,}/', $word)) { // Jika awalan ?me-?,

            if (preg_match('/^(me)[lrwyv][aiueo]/', $word)) { // aturan 10
                $word1 = preg_replace('/^(me)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(mem)[bfvp]\S{1,}/', $word)) { // aturan 11.
                $word1 = preg_replace('/^(mem)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }
            if (preg_match('/^(mempe)\S{1,}/', $word)) { // aturan 12
                $word1 = preg_replace('/^(mem)/', 'pe', $word);

                if (checkDict($word1)) {

                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }
            if (preg_match('/^(mem)((r[aiueo])|[aiueo])\S{1,}/', $word)) { //aturan 13
                $word1 = preg_replace('/^(mem)/', 'm', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(mem)/', 'p', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(men)[cdjszt]\S{1,}/', $word)) { // aturan 14.
                $word1 = preg_replace('/^(men)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(men)[aiueo]\S{1,}/', $word)) { //aturan 15
                $word1 = preg_replace('/^(men)/', 'n', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(men)/', 't', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(meng)[ghqk]\S{1,}/', $word)) { // aturan 16.
                $word1 = preg_replace('/^(meng)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(meng)[aiueo]\S{1,}/', $word)) { // aturan 17
                $word1 = preg_replace('/^(meng)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(meng)/', 'k', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(menge)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(meny)[aiueo]\S{1,}/', $word)) { // aturan 18.
                $word1 = preg_replace('/^(meny)/', 's', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(me)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }
        }
        /*------------end ?me-?, ---------------------------------------------*/

        /*------------ Awalan ?pe-?, ---------------------------------------------*/
        if (preg_match('/^(pe)\S{1,}/', $word)) { // Jika awalan ?pe-?,

            if (preg_match('/^(pe)[wy]\S{1,}/', $word)) { // aturan 20.
                $word1 = preg_replace('/^(pe)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(per)[aiueo]\S{1,}/', $word)) { // aturan 21
                $word1 = preg_replace('/^(per)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(per)/', 'r', $word);
                if (checkDict($word1)) {
                    return $word1;
                }
                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }
            if (preg_match('/^(per)[^aiueor][[:alpha:]](?!er)\S{1,}/', $word)) { // aturan  23
                $word1 = preg_replace('/^(per)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(per)[^aiueor][[:alpha:]](er)[aiueo]\S{1,}/', $word)) { // aturan  24
                $word1 = preg_replace('/^(per)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(pem)[bfv]\S{1,}/', $word)) { // aturan  25
                $word1 = preg_replace('/^(pem)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(pem)(r[aiueo]|[aiueo])\S{1,}/', $word)) { // aturan  26
                $word1 = preg_replace('/^(pem)/', 'm', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(pem)/', 'p', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(pen)[cdjzt]\S{1,}/', $word)) { // aturan  27
                $word1 = preg_replace('/^(pen)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(pen)[aiueo]\S{1,}/', $word)) { // aturan  28
                $word1 = preg_replace('/^(pen)/', 'n', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(pen)/', 't', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(peng)[^aiueo]\S{1,}/', $word)) { // aturan  29
                $word1 = preg_replace('/^(peng)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(peng)[aiueo]\S{1,}/', $word)) { // aturan  30
                $word1 = preg_replace('/^(peng)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(peng)/', 'k', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(penge)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(peny)[aiueo]\S{1,}/', $word)) { // aturan  31
                $word1 = preg_replace('/^(peny)/', 's', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
                $word1 = preg_replace('/^(pe)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(pel)[aiueo]\S{1,}/', $word)) { // aturan  32
                $word1 = preg_replace('/^(pel)/', 'l', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(pelajar)\S{0,}/', $word)) {
                $word1 = preg_replace('/^(pel)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(pe)[^rwylmn]er[aiueo]\S{1,}/', $word)) { // aturan  33
                $word1 = preg_replace('/^(pe)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(pe)[^rwylmn](?!er)\S{1,}/', $word)) { // aturan  34
                $word1 = preg_replace('/^(pe)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }

            if (preg_match('/^(pe)[^aiueor]er[^aiueo]\S{1,}/', $word)) { // aturan  36
                $word1 = preg_replace('/^(pe)/', '', $word);
                if (checkDict($word1)) {
                    return $word1;
                }

                $word2 = removeDerivationSuffix($word1);
                if (checkDict($word2)) {
                    return $word2;
                }
            }
        }
    }
    /*------------end ?pe-?, ---------------------------------------------*/
    /*------------ Awalan ?memper-?, ---------------------------------------------*/
    if (preg_match('/^(memper)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(memper)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
        //*-- Cek luluh -r ----------
        $word1 = preg_replace('/^(memper)/', 'r', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    if (preg_match('/^(mempel)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(mempel)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
        //*-- Cek luluh -r ----------
        $word1 = preg_replace('/^(mempel)/', 'l', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    if (preg_match('/^(menter)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(menter)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
        //*-- Cek luluh -r ----------
        $word1 = preg_replace('/^(menter)/', 'r', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    if (preg_match('/^(member)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(member)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
        //*-- Cek luluh -r ----------
        $word1 = preg_replace('/^(member)/', 'r', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    /*------------end ?diper-?, ---------------------------------------------*/
    if (preg_match('/^(diper)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(diper)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
        /*-- Cek luluh -r ----------*/
        $word1 = preg_replace('/^(diper)/', 'r', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    /*------------end ?diper-?, ---------------------------------------------*/
    /*------------end ?diter-?, ---------------------------------------------*/
    if (preg_match('/^(diter)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(diter)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
        /*-- Cek luluh -r ----------*/
        $word1 = preg_replace('/^(diter)/', 'r', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    /*------------end ?diter-?, ---------------------------------------------*/
    /*------------end ?dipel-?, ---------------------------------------------*/
    if (preg_match('/^(dipel)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(dipel)/', 'l', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
        /*-- Cek luluh -l----------*/
        $word1 = preg_replace('/^(dipel)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    /*------------end ?dipel-?, ---------------------------------------------*/
    if (preg_match('/^(diber)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(diber)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
        /*-- Cek luluh -l----------*/
        $word1 = preg_replace('/^(diber)/', 'r', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    if (preg_match('/^(keber)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(keber)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
        /*-- Cek luluh -l----------*/
        $word1 = preg_replace('/^(keber)/', 'r', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    if (preg_match('/^(keter)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(keter)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
        /*-- Cek luluh -l----------*/
        $word1 = preg_replace('/^(keter)/', 'r', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    if (preg_match('/^(berke)\S{1,}/', $word)) {
        $word1 = preg_replace('/^(berke)/', '', $word);
        if (checkDict($word1)) {
            return $word1;
        }
        $word2 = removeDerivationSuffix($word1);
        if (checkDict($word2)) {
            return $word2;
        }
    }
    /* --- Cek Ada Tidaknya Prefik/Awalan (?di-?, ?ke-?, ?se-?, ?te-?, ?be-?, ?me-?, atau ?pe-?) ------*/
    if (preg_match('/^(di|[kstbmp]e)\S{1,}/', $word) == false) {
        return $word0;
    }

    return $word0;
}
